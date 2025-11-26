<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Sentiment Analysis Service
 * Analyzes customer messages for sentiment, emotion, and urgency
 */
class SentimentAnalysisService
{
    /**
     * Analyze sentiment of customer message
     */
    public function analyzeSentiment(string $message): array
    {
        $cacheKey = "sentiment_" . md5($message);
        
        return Cache::remember($cacheKey, 3600, function() use ($message) {
            // For now, use a rule-based approach
            // In production, this could integrate with services like:
            // - Google Cloud Natural Language API
            // - AWS Comprehend
            // - Azure Text Analytics
            // - OpenAI API for sentiment analysis
            
            return $this->ruleBasedSentimentAnalysis($message);
        });
    }

    /**
     * Rule-based sentiment analysis
     */
    protected function ruleBasedSentimentAnalysis(string $message): array
    {
        $message_lower = strtolower($message);
        
        // Positive indicators
        $positiveWords = [
            'great', 'excellent', 'amazing', 'awesome', 'fantastic', 'wonderful', 
            'perfect', 'love', 'like', 'happy', 'satisfied', 'pleased', 'thank',
            'thanks', 'good', 'nice', 'brilliant', 'outstanding', 'superb'
        ];
        
        // Negative indicators
        $negativeWords = [
            'terrible', 'awful', 'horrible', 'bad', 'worst', 'hate', 'angry',
            'frustrated', 'disappointed', 'upset', 'problem', 'issue', 'broken',
            'defective', 'wrong', 'error', 'complaint', 'refund', 'cancel', 'return'
        ];
        
        // Urgency indicators
        $urgentWords = [
            'urgent', 'emergency', 'asap', 'immediately', 'now', 'quick', 'fast',
            'help', 'stuck', 'stopped working', 'not working', 'broken'
        ];
        
        // Question indicators
        $questionWords = [
            'how', 'what', 'when', 'where', 'why', 'which', 'can you', 'could you',
            'would you', 'do you', 'is it', 'are there', '?'
        ];

        $positiveScore = $this->countWordMatches($message_lower, $positiveWords);
        $negativeScore = $this->countWordMatches($message_lower, $negativeWords);
        $urgencyScore = $this->countWordMatches($message_lower, $urgentWords);
        $questionScore = $this->countWordMatches($message_lower, $questionWords);

        // Calculate overall sentiment
        $netSentiment = $positiveScore - $negativeScore;
        
        if ($netSentiment > 1) {
            $sentiment = 'positive';
            $confidence = min(0.9, 0.6 + ($netSentiment * 0.1));
        } elseif ($netSentiment < -1) {
            $sentiment = 'negative';
            $confidence = min(0.9, 0.6 + (abs($netSentiment) * 0.1));
        } else {
            $sentiment = 'neutral';
            $confidence = 0.5 + (abs($netSentiment) * 0.1);
        }

        // Determine emotion category
        $emotion = $this->determineEmotion($message_lower, $positiveScore, $negativeScore, $urgencyScore);
        
        // Determine intent confidence
        $intentConfidence = $questionScore > 0 ? 'question' : 
                           ($urgencyScore > 0 ? 'support_needed' : 'general');

        return [
            'label' => $sentiment,
            'confidence' => round($confidence, 2),
            'emotion' => $emotion,
            'urgency_level' => $this->determineUrgencyLevel($urgencyScore, $negativeScore),
            'intent_type' => $intentConfidence,
            'scores' => [
                'positive' => $positiveScore,
                'negative' => $negativeScore,
                'urgency' => $urgencyScore,
                'question' => $questionScore
            ]
        ];
    }

    /**
     * Count word matches in message
     */
    protected function countWordMatches(string $message, array $words): int
    {
        $count = 0;
        foreach ($words as $word) {
            if (str_contains($message, $word)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Determine specific emotion
     */
    protected function determineEmotion(string $message, int $positive, int $negative, int $urgency): string
    {
        if ($urgency > 1 && $negative > 0) return 'frustrated';
        if ($negative > 2) return 'angry';
        if ($negative > 0) return 'dissatisfied';
        if ($positive > 2) return 'delighted';
        if ($positive > 0) return 'satisfied';
        if (str_contains($message, '?')) return 'curious';
        return 'neutral';
    }

    /**
     * Determine urgency level
     */
    protected function determineUrgencyLevel(int $urgencyScore, int $negativeScore): string
    {
        if ($urgencyScore > 1 || ($urgencyScore > 0 && $negativeScore > 1)) {
            return 'high';
        } elseif ($urgencyScore > 0 || $negativeScore > 0) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Get recommended response strategy based on sentiment
     */
    public function getResponseStrategy(array $sentimentAnalysis): array
    {
        $sentiment = $sentimentAnalysis['label'] ?? 'neutral';
        $emotion = $sentimentAnalysis['emotion'] ?? 'neutral';
        $urgency = $sentimentAnalysis['urgency_level'] ?? 'low';

        $strategy = [
            'tone' => 'friendly',
            'priority' => 'normal',
            'approach' => 'helpful',
            'escalation_needed' => false
        ];

        switch ($sentiment) {
            case 'positive':
                $strategy['tone'] = 'enthusiastic';
                $strategy['approach'] = 'capitalize_on_mood';
                break;
                
            case 'negative':
                $strategy['tone'] = 'empathetic';
                $strategy['priority'] = 'high';
                $strategy['approach'] = 'problem_solving';
                if ($urgency === 'high') {
                    $strategy['escalation_needed'] = true;
                }
                break;
                
            case 'neutral':
                $strategy['tone'] = 'professional';
                $strategy['approach'] = 'informative';
                break;
        }

        // Adjust based on emotion
        if ($emotion === 'frustrated' || $emotion === 'angry') {
            $strategy['tone'] = 'very_empathetic';
            $strategy['priority'] = 'urgent';
            $strategy['escalation_needed'] = true;
        }

        return $strategy;
    }
}
