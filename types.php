<?php
/**
 * Every request to the akit API returns an object with this structure
 */
class SessionResponse {
    public $success;
    public $msg;
    public $sessions;
}

/**
 * Request body for a request to create a session for an exercise
 */
class CreateSessionRequestBody {
    public $apiVersion = 2;

    /**
     * Should be an array of ExerciseDef objects
     */
    public $exercises;

    public function __construct(array $exercises, $attributes=null) {
        $this->exercises = $exercises;
        $this->attributes = $attributes;
    }
}

/**
 * Tuple containing exercise ID and version
 */
class ExerciseDef {
    public $exerciseId;
    public $version;
    public $solutionMode;

    public function __construct($exerciseId, $version, $solutionMode) {
        $this->exerciseId = $exerciseId;
        $this->solutionMode = $solutionMode;
        $this->version = isset($version) && !empty($version) ? $version : 'latest';
    }
}

?>