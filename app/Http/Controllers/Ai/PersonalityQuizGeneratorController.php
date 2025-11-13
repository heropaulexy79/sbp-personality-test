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

        // We merge the system prompt and user query into a single prompt
        // as the 'systemInstruction' field can sometimes cause issues depending on API version.
        $systemPrompt = "You are an expert in occupational psychology. Generate a 12-question personality quiz based on the 12 provided Nigerian Corporate Archetypes. Return ONLY JSON matching the requested schema.";
        $userQuery = "Archetypes: {$archetypesList}. Generate the quiz package.";

        $fullPrompt = $systemPrompt . "\n\n" . $userQuery;

        $payload = [
             'contents' => [
                 [
                     'role' => 'user',
                     'parts' => [
                         ['text' => $fullPrompt]
                     ]
                 ]
             ],
             'generationConfig' => [
                 'responseMimeType' => 'application/json',
                 'responseSchema' => $responseSchema,
             ],
        ];

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
     * Generate the personality quiz JSON via AI and update the existing Lesson.
     * (Used on the Quiz Edit Page)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Lesson  $lesson
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateAndUpdate(Request $request, Lesson $lesson)
    {
        // 1. Call the existing generate function to get the content
        $response = $this->generate($request);
        $quizData = $response->getData(true);

        // 2. Handle failure from the generate method
        if ($response->getStatusCode() !== 200 || isset($quizData['error'])) {
             return back()->with('error', $quizData['error'] ?? 'AI generation failed.')->withStatus($response->getStatusCode());
        }

        try {
             $incomingData = $quizData;
             $questions = $incomingData['questions'] ?? [];
             
             // Map 'archetypes' from AI response to 'traits' and include 'archetypes' for compatibility
             $traitsToSave = $incomingData['archetypes'] ?? [];

             $finalQuizData = [
                 'traits' => $traitsToSave,
                 'questions' => $questions,
                 'archetypes' => $traitsToSave, // Ensure 'archetypes' key is saved for consistency
             ];

             if (empty($finalQuizData['questions'])) {
                  Log::error('Update failed: Questions array is empty after AI processing.');
                  return back()->with('error', 'Cannot save quiz: No questions data found from AI.');
             }

             // 3. Update the existing Lesson
             $lesson->content_json = $finalQuizData;
             // FIX: Ensure the lesson is explicitly published after a successful update (if it wasn't already)
             $lesson->is_published = true; 
             $lesson->save();
             
             // 4. Reload the page with the new content
             return back()->with('success', 'Personality Quiz content successfully generated and updated!');

        } catch (\Exception $e) {
             Log::error('Failed to update personality quiz lesson with AI content', ['error' => $e->getMessage()]);
             return back()->with('error', 'Failed to update quiz with AI content.');
        }
    }

    /**
     * Generate the personality quiz JSON via AI and store it as a new Lesson.
     * (Used on the Quiz Create Page)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateAndStore(Request $request)
    {
        // 1. Validate the minimum required input (the title)
        $request->validate([
             'title' => 'required|string|max:255',
        ]);
        
        // 2. Call the existing generate function to get the quiz content
        $response = $this->generate($request);
        $quizData = $response->getData(true);

        if ($response->getStatusCode() !== 200 || isset($quizData['error'])) {
             // If AI generation fails, redirect back with the error message
             return back()->with('error', $quizData['error'] ?? 'AI generation failed.')->withStatus($response->getStatusCode());
        }

        try {
             $incomingData = $quizData;
             $questions = $incomingData['questions'] ?? [];
             $traitsToSave = $incomingData['archetypes'] ?? [];

             $finalQuizData = [
                 'questions' => $questions,
                 'traits' => $traitsToSave,
                 'archetypes' => $traitsToSave, // Ensure 'archetypes' key is saved for consistency
             ];

             if (empty($finalQuizData['questions'])) {
                  Log::error('Store failed: Questions array is empty after AI processing.');
                  return back()->with('error', 'Cannot create quiz: No questions data found from AI.');
             }

             // 3. Create a new Lesson (Quiz) record
             $lesson = new Lesson();
             // Note: Assuming it's a standalone quiz, course_id remains null for this flow
             $lesson->course_id = null; 
             $lesson->title = $request->title;
             // The slug is generated here
             $lesson->slug = Str::slug($request->title) . '-' . Str::random(6);
             $lesson->type = 'personality_quiz';
             // FIX: Set to published immediately so the creator can share and users can enroll.
             $lesson->is_published = true; 
             $lesson->user_id = auth()->id(); 
             $lesson->content_json = $finalQuizData;
             $lesson->save();

             // 4. Redirect to the newly created quiz's edit page, using the SLUG
             return redirect()
                 ->route('quizzes.edit', $lesson->slug)
                 ->with('success', 'Personality Quiz successfully generated and created! It is now published.');

        } catch (\Exception $e) {
             Log::error('Failed to save personality quiz lesson after AI generation', ['error' => $e->getMessage()]);
             return back()->with('error', 'Failed to save the new quiz after generation.');
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
             'quiz_data' => 'required|array',
        ]);

        try {
             $incomingData = $request->input('quiz_data');

             // *** DEBUG LOGGING RETAINED HERE ***
             $questions = $incomingData['questions'] ?? [];
             $traits = $incomingData['traits'] ?? $incomingData['archetypes'] ?? [];

             Log::info('Personality Quiz Save Debug:', [
                 'questions_count' => count($questions),
                 'traits_count' => count($traits),
                 'questions_content' => json_encode($questions), // Logs the full question content
             ]);
             // *******************************

             $lesson = new Lesson();
             $lesson->course_id = $request->course_id;
             $lesson->title = $request->title;
             $lesson->slug = Str::slug($request->title) . '-' . Str::random(6);
             $lesson->type = 'personality_quiz';
             $lesson->is_published = true; // This method was already correct
             $lesson->user_id = auth()->id();

             // SAFER MAPPING LOGIC:
             // Prefer 'traits' if explicitly set, otherwise fallback to 'archetypes'
             $traitsToSave = !empty($incomingData['traits']) ? $incomingData['traits'] : ($incomingData['archetypes'] ?? []);

             $finalQuizData = [
                 'traits' => $traitsToSave,
                 'questions' => $questions, // Use the extracted and logged variable
                 'archetypes' => $traitsToSave, // ADDED: Ensure 'archetypes' key is present for full consistency
             ];

             // Final validation check
             if (empty($finalQuizData['questions'])) {
                  Log::error('Save failed: Questions array is empty after processing.', ['incoming_keys' => array_keys($incomingData)]);
                  return response()->json(['error' => 'Cannot save quiz: No questions data found.'], 422);
             }

             $lesson->content_json = $finalQuizData;
             $lesson->save();

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