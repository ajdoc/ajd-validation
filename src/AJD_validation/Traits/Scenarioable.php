<?php

namespace AJD_validation\Traits;

trait Scenarioable
{
    /**
     * Process scenario array
     *
     * @param  array $props
     * @param  int $index
     * @param  string $ruleName
     * @param  string $rawRuleName
     * @param  array $details
     * @return array
     */
    public function processScenarios(array $props, $index, $ruleName, $rawRuleName, array $details = [])
    {
        $sometimes = [];
        $groups = [];
        $generator = [];
        $suspend = [];
        $stopOnError = [];
        
        $sometimes[] = $props['sometimes'][ $ruleName ][$index] ?? null;
        $groups[] = $props['groups'][ $ruleName ][$index] ?? [];
        
        $stopOnError[] = $props['stop_on_error'][ $ruleName ][$index] ?? [];
        $generator[] = static::$generators[$rawRuleName][$index] ?? [];
        $suspend[] = static::$ajd_prop['fiber_suspend'][$ruleName][$index] ?? [];

        $scenarios = [
            'sometimes' => $sometimes ?? [],
            'groups' => $groups,
            'generator' => $generator,
            'suspend' => $suspend,
            'stopOnError' => $stopOnError
        ];

        return $scenarios;
    }

    /**
     * Checks if rule will run a certain scenario
     *
     * @param  array $scenarios
     * @return \AJD_validation\Helpers\Rule_scenario
     */
    public function checkScenarios(array $scenarios = [])
    {
        if(empty($scenarios))
        {
            return $this;
        }

        foreach($scenarios as $scenario => $values)
        {
            $bool = (isset($values[0]) && !empty($values[0]));

            if($scenario == 'groups')
            {
                $this->runif($bool)->{$scenario}($values[0] ?? null);    
            }
            else
            {
                $this->runif($bool)->{$scenario}(...$values);
            }   
        }

        return $this;
    }
}