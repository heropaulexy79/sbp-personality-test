<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PersonalityTrait;
use App\Models\Lesson;
use Illuminate\Support\Str;

class PersonalityQuizGeneratorController extends Controller
{
    /**
     * Generate the personality quiz JSON via AI.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request)
    {
        // Increase execution time to 5 minutes for this request
        set_time_limit(300);

        // 1. Fetch Archetypes from Database directly
        $archetypes = PersonalityTrait::all()->pluck('name')->toArray();

        // 2. Validate we have enough archetypes seeded
        if (count($archetypes) < 12) {
             Log::error('Not enough archetypes found in DB for quiz generation.', ['count' => count($archetypes)]);
             return response()->json(['error' => 'System error: Not enough archetypes configured.'], 500);
        }

        $archetypesList = implode(", ", $archetypes);

        // 3. Get API Key
        $apiKey = config('services.gemini.api_key');
        if (empty($apiKey)) {
            return response()->json(['error' => 'AI service is not configured correctly.'], 500);
        }

        // Updated to use a stable model version
        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

        $responseSchema = [
            'type' => 'OBJECT',
            'properties' => [
                'archetypes' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'name' => ['type' => 'STRING'],
                            'description' => ['type' => 'STRING']
                        ],
                        'required' => ['name', 'description']
                    ],
                ],
                'questions' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'question_text' => ['type' => 'STRING'],
                            'options' => [
                                'type' => 'ARRAY',
                                'items' => [
                                    'type' => 'OBJECT',
                                    'properties' => [
                                        'option_text' => ['type' => 'STRING'],
                                        'maps_to_archetype' => ['type' => 'STRING']
                                    ],
                                    'required' => ['option_text', 'maps_to_archetype']
                                ],
                            ]
                        ],
                        'required' => ['question_text', 'options']
                    ],
                ]
            ],
            'required' => ['archetypes', 'questions']
        ];

        // --- START OF FIX ---
        // We merge the system prompt and user query into a single prompt
        // as the 'systemInstruction' field was causing a 400 error.

        $systemPrompt = "You are an expert in occupational psychology. Generate a 12-question personality quiz based on the 12 provided Nigerian Corporate Archetypes. Return ONLY JSON matching the requested schema.";
        $userQuery = "Archetypes: {$archetypesList}. Generate the quiz package.";

        // MERGE PROMPTS: Combine system and user prompts for reliable execution
        $fullPrompt = $systemPrompt . "\n\n" . $userQuery;

        $payload = [
            'contents' => [
                [
                    'role' => 'user', // Send the combined prompt as the user
                    'parts' => [
                        ['text' => $fullPrompt]
                    ]
                ]
            ],
            // 'systemInstruction' field is removed
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => $responseSchema,
            ],
        ];
        // --- END OF FIX ---

        try {
            $response = Http::withOptions(['timeout' => 120])->retry(3, 1000)->post($apiUrl, $payload);

            if (!$response->successful()) {
                Log::error('Gemini API request failed', ['body' => $response->body()]);
                return response()->json(['error' => 'Failed to generate quiz.'], $response->status());
            }

            $result = $response->json();
            if (empty($result['candidates'][0]['content']['parts'][0]['text'])) {
                return response()->json(['error' => 'AI returned empty content.'], 500);
            }

            $quizData = json_decode($result['candidates'][0]['content']['parts'][0]['text'], true);

            // Validate if quiz data is correctly decoded
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($quizData)) {
                Log::error('Failed to decode AI response JSON', ['response_text' => $result['candidates'][0]['content']['parts'][0]['text'] ?? 'NULL']);
                return response()->json(['error' => 'AI returned invalid JSON format.'], 500);
            }

            return response()->json($quizData);

        } catch (\Exception $e) {
            Log::error('Quiz Generation Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    /**
     * Save the generated JSON as a new Personality Quiz Lesson.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'course_id' => 'nullable|exists:courses,id',
            'quiz_data' => 'required|array', // The JSON returned by the generate method
        ]);

        try {
            $lesson = new Lesson();
            $lesson->course_id = $request->course_id;
            $lesson->title = $request->title;
            // Generate a unique slug
            $lesson->slug = Str::slug($request->title) . '-' . Str::random(6);
            // Ensure this type matches the one queried by QuizController@index
            $lesson->type = 'personality_quiz';
            // Store the entire quiz JSON package in the content field
            $lesson->content = json_encode($request->quiz_data);
            $lesson->is_published = true; // Or false if you want to draft it first
            $lesson->save();

            // UPDATED: Change the redirect_url to the named 'dashboard' route
            return response()->json([
                'message' => 'Personality Quiz saved successfully!',
                'lesson_id' => $lesson->id,
                'redirect_url' => route('dashboard')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to save personality quiz lesson', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to save quiz.'], 500);
        }
    }
}