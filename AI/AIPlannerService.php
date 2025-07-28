<?php
require_once __DIR__ . '/LLMService.php';

class AIPlannerService {
    private $tourData;
    private $llmService;

    public function __construct() {
        // Load tour data from JSON
        $tourJson = file_get_contents(__DIR__ . '/../Journey/tour.json');
        $this->tourData = json_decode($tourJson, true);
        $this->llmService = new LLMService();
    }

    /**
     * Generates a travel plan based on the user's query.
     * @param string $query The user's natural language query.
     * @return array The generated travel plan.
     */
    public function generatePlan($query) {
        try {
            // Process query with LLM
            $llmResponse = $this->llmService->processQuery($query);

            // Validate and enhance response with tour.json data
            $destination = strtolower($llmResponse['destination']);
            $cityKey = $this->mapDestinationToCityKey($destination);

            if (!$cityKey || !isset($this->tourData[$cityKey])) {
                return [
                    'error' => 'Sorry, we don’t have tours for ' . $llmResponse['destination'] . '. Try another destination.'
                ];
            }

            // Filter tours based on LLM response (e.g., budget, duration)
            $filteredTours = $this->filterTours($cityKey, $llmResponse['budget'], $llmResponse['duration']);

            // Combine LLM plan with filtered tours
            $plan = $this->enhancePlanWithTours($llmResponse['suggested_plan'], $filteredTours);

            return [
                'plan' => $plan,
                'summary' => [
                    'destination' => $llmResponse['destination'],
                    'duration' => $llmResponse['duration'],
                    'budget' => $llmResponse['budget'],
                    'group_type' => $llmResponse['group_type'],
                    'total_cost' => array_sum(array_column($plan, 'cost'))
                ],
                'tips' => $llmResponse['tips']
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Maps LLM-detected destination to tour.json city key.
     * @param string $destination The destination from LLM.
     * @return string|null The corresponding city key.
     */
    private function mapDestinationToCityKey($destination) {
        $destinationMap = [
            'dalat' => 'dalat',
            'ha giang' => 'hagiang',
            'ho chi minh' => 'hcm',
            'hoi an' => 'hoian',
            'hue' => 'hue',
            'nha trang' => 'nhatrang',
            'phu quoc' => 'phuquoc',
            'phu yen' => 'phuyen',
            'tay bac' => 'taybac',
            // Add more mappings as needed
        ];
        return $destinationMap[strtolower($destination)] ?? null;
    }

    /**
     * Filters tours from tour.json based on budget and duration.
     * @param string $cityKey The city key in tour.json.
     * @param int $budget The user's budget in VND.
     * @param int $duration The trip duration in days.
     * @return array Filtered tours.
     */
    private function filterTours($cityKey, $budget, $duration) {
        $tours = $this->tourData[$cityKey]['tours'];
        return array_filter($tours, function ($tour) use ($budget, $duration) {
            return $tour['price'] <= $budget && $tour['duration'] <= $duration;
        });
    }

    /**
     * Enhances the LLM-generated plan with tour data.
     * @param array $llmPlan The plan from the LLM.
     * @param array $tours Available tours from tour.json.
     * @return array Enhanced plan.
     */
    private function enhancePlanWithTours($llmPlan, $tours) {
        // If tours are available, adjust the plan to include tour-specific details
        $enhancedPlan = $llmPlan;
        if (!empty($tours)) {
            $tour = reset($tours); // Use the first matching tour for simplicity
            foreach ($enhancedPlan as &$day) {
                $day['notes'] .= " (Bookable via VietTransit tour: {$tour['title']})";
                $day['cost'] = min($day['cost'], $tour['price'] / count($llmPlan)); // Distribute tour cost
            }
        }
        return $enhancedPlan;
    }
}
?>