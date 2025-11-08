<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Added for logging
use App\Models\Lesson; // Keep Lesson model for potential future use or context
use Illuminate\Support\Facades\Gate; // Keep Gate for potential future use or context

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
        $request->validate([
            'archetypes' => 'required|array|min:12',
            'archetypes.*' => 'string',
        ]);

        $archetypesList = implode(", ", $request->input('archetypes'));
        $apiKey = ''; // API key is handled by the environment
        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=' . $apiKey;

        // Define the structured JSON response we want from the AI
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

        // Define the prompt and system instructions for the AI
        $systemPrompt = "You are an expert in occupational psychology and quiz design, specializing in Nigerian corporate culture.
Your task is to generate a 12-question personality quiz based on the 12 Nigerian Corporate Archetypes provided.
- You MUST generate a 2-3 sentence description for each of the 12 archetypes.
- You MUST generate exactly 12 scenario-based questions (e.g., 'When faced with a tight deadline, you are most likely to...').
- Each question MUST have exactly 4 multiple-choice options.
- Each of the 4 options MUST be mapped directly to *one* of the 12 provided personality archetypes. An option cannot map to more than one archetype.
- Your goal is to create questions where the answers help determine which archetype the user fits most.
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
            // Use exponential backoff for retries
            $response = Http::withOptions(['timeout' => 120]) // Increase timeout for long generation
                ->retry(3, 1000, function ($exception, $request) {
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException || $exception instanceof \Illuminate\Http\Client\RequestException;
                }, false) // Use exponential backoff
                ->post($apiUrl, $payload);

            if (!$response->successful()) {
                Log::error('Gemini API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return response()->json(['error' => 'Failed to generate quiz. The AI service returned an error.'], $response->status());
            }

            $result = $response->json();

            if (empty($result['candidates'][0]['content']['parts'][0]['text'])) {
                 Log::error('Gemini API returned empty content', ['response' => $result]);
                return response()->json(['error' => 'Failed to parse AI response. The AI returned empty content.'], 500);
            }

            // The response text *is* the JSON string
            $jsonString = $result['candidates'][0]['content']['parts'][0]['text'];
            $quizData = json_decode($jsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to decode JSON from Gemini', ['json_string' => $jsonString, 'error' => json_last_error_msg()]);
                return response()->json(['error' => 'Failed to parse AI response. Invalid JSON format.'], 500);
            }

            // Validate the structure we received
            if (empty($quizData['archetypes']) || empty($quizData['questions'])) {
                 Log::error('Gemini API returned incomplete JSON structure', ['data' => $quizData]);
                 return response()->json(['error' => 'Failed to parse AI response. Incomplete data.'], 500);
            }

            return response()->json($quizData);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Gemini API connection error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Could not connect to AI service. Please try again later.'], 504); // Gateway Timeout
        } catch (\Exception $e) {
            Log::error('PersonalityQuizGeneratorController error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
}