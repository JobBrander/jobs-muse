<?php namespace JobBrander\Jobs\Client\Providers;

use JobBrander\Jobs\Client\Job;

class Muse extends AbstractProvider
{
    /**
     * Returns the standardized job object
     *
     * @param array $payload
     *
     * @return \JobBrander\Jobs\Client\Job
     */
    public function createJobObject($payload)
    {
        $defaults = [
            'jobTitle',
            'company',
            'location',
            'date',
            'detailUrl'
        ];

        $payload = static::parseAttributeDefaults($payload, $defaults);

        $job = new Job([
            'title' => $payload['jobTitle'],
            'name' => $payload['jobTitle'],
            'url' => $payload['detailUrl'],
            'location' => $payload['location'],
        ]);

        $location = static::parseLocation($payload['location']);

        $job->setCompany($payload['company'])
            ->setDatePostedAsString($payload['date']);

        if (isset($location[0])) {
            $job->setCity($location[0]);
        }
        if (isset($location[1])) {
            $job->setState($location[1]);
        }

        return $job;
    }

    /**
     * Get data format
     *
     * @return string
     */
    public function getFormat()
    {
        return 'json';
    }

    /**
     * Get listings path
     *
     * @return  string
     */
    public function getListingsPath()
    {
        return 'resultItemList';
    }

    /**
     * Get query string for client based on properties
     *
     * @return string
     */
    public function getQueryString()
    {
        $query_params = [
            'text' => 'getKeyword',
            'state' => 'getState',
            'city' => 'getCity',
            'page' => 'getPage',
            'pgcnt' => 'getCount',
        ];

        $query_string = [];

        array_walk($query_params, function ($value, $key) use (&$query_string) {
            $computed_value = $this->$value();
            if (!is_null($computed_value)) {
                $query_string[$key] = $computed_value;
            }
        });

        return http_build_query($query_string);
    }

    /**
     * Get url
     *
     * @return  string
     */
    public function getUrl()
    {
        $query_string = $this->getQueryString();

        return 'http://service.dice.com/api/rest/jobsearch/v1/simple.json?'.$query_string;
    }

    /**
     * Get http verb
     *
     * @return  string
     */
    public function getVerb()
    {
        return 'GET';
    }
}
