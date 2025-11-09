<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PersonalityTrait; // Ensure this is imported

class PersonalityQuizGeneratorController extends Controller
{
    /**
     * Handle the incoming request to generate a personality quiz.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        // 1. Fetch Archetypes from Database directly
        // We no longer rely on the user to send them in the request.
        set_time_limit(300);
        $archetypes = PersonalityTrait::all()->pluck('name')->toArray();

        // 2. Validate we have enough archetypes seeded
        if (count($archetypes) < 12) {
             Log::error('Not enough archetypes found in DB for quiz generation.', ['count' => count($archetypes)]);
             return response()->json(['error' => 'System error: Not enough archetypes configured. Please contact support.'], 500);
        }

        $archetypesList = implode(", ", $archetypes);

        // 3. Get API Key from Config
        $apiKey = config('services.gemini.api_key');

        if (empty($apiKey)) {
            Log::critical('GEMINI_API_KEY is missing in configuration.');
            return response()->json(['error' => 'AI service is not configured correctly.'], 500);
        }

        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

        // --- Schema Definition (Unchanged) ---
        $responseSchema = [
            'type' => 'OBJECT',
            'properties' => [
                'archetypes' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'name' => ['type' => 'STRING'],
                            'description' => [
                                'type' => 'STRING',
                                'description' => 'A 2-3 sentence description of this archetype, based on the Nigerian corporate context.'
                            ]
                        ],
                        'required' => ['name', 'description']
                    ],
                ],
                'questions' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'question_text' => ['type' => 'STRING', 'description' => 'A scenario-based question relevant to a workplace.'],
                            'options' => [
                                'type' => 'ARRAY',
                                'items' => [
                                    'type' => 'OBJECT',
                                    'properties' => [
                                        'option_text' => ['type' => 'STRING'],
                                        'maps_to_archetype' => [
                                            'type' => 'STRING',
                                            'description' => 'The exact name of one of the 12 archetypes this option maps to.'
                                        ]
                                    ],
                                    'required' => ['option_text', 'maps_to_archetype']
                                ],
                                'minItems' => 4,
                                'maxItems' => 4
                            ]
                        ],
                        'required' => ['question_text', 'options']
                    ],
                    'minItems' => 12,
                    'maxItems' => 12
                ]
            ],
            'required' => ['archetypes', 'questions']
        ];

        // --- Prompt Definition (Unchanged) ---
        $systemPrompt = "You are an expert in occupational psychology and quiz design, specializing in Nigerian corporate culture.
Your task is to generate a 12-question personality quiz based on the 12 Nigerian Corporate Archetypes provided.
- You MUST generate a 2-3 sentence description for each of the 12 archetypes.
- You MUST generate exactly 12 scenario-based questions.
- Each question MUST have exactly 4 multiple-choice options.
- Each of the 4 options MUST be mapped directly to *one* of the 12 provided personality archetypes.
- You MUST return the data in the requested JSON schema.";

        $userQuery = "Here are the 12 Nigerian Corporate Archetypes: {$archetypesList}.
Please generate the complete personality quiz package (archetype descriptions and 12 questions) based on these archetypes.";

        $payload = [
            'contents' => [
                ['parts' => [['text' => $userQuery]]]
            ],
            'systemInstruction' => [
                'parts' => [['text' => $systemPrompt]]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => $responseSchema,
            ],
        ];

        try {
            $response = Http::withOptions(['timeout' => 120])
                ->retry(3, 1000)
                ->post($apiUrl, $payload);

            if (!$response->successful()) {
                Log::error('Gemini API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json(['error' => 'Failed to generate quiz. Please try again later.'], $response->status());
            }

            $result = $response->json();

            if (empty($result['candidates'][0]['content']['parts'][0]['text'])) {
                 Log::error('Gemini API returned empty content', ['response' => $result]);
                return response()->json(['error' => 'AI returned empty content.'], 500);
            }

            $jsonString = $result['candidates'][0]['content']['parts'][0]['text'];
            $quizData = json_decode($jsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to decode JSON from Gemini', ['error' => json_last_error_msg()]);
                return response()->json(['error' => 'Invalid response format from AI.'], 500);
            }

            return response()->json($quizData);

        } catch (\Exception $e) {
            Log::error('PersonalityQuizGeneratorController error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
}