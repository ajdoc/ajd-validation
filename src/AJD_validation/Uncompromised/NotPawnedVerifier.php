<?php namespace AJD_validation\Uncompromised;

use AJD_validation\Contracts\Uncompromised_interface;

class NotPawnedVerifier implements Uncompromised_interface
{
	protected $threshold;

	public function __construct($threshold = 0)
	{
		$this->threshold = $threshold;
	}

	/**
     * Verify that the given data has not been compromised in public breaches.
     *
     * @param  array  $data
     * @return bool
     */
    public function verify($value)
    {
    	if (empty($value = (string) $value)) 
    	{
            return false;
        }

        [$hash, $hashPrefix] = $this->getHash($value);

        $response 	= $this->makeRequest($hashPrefix);

    	return $this->search($response, $hashPrefix, $hash);
    }

	protected function search($response, $hashPrefix, $hash)
	{
		$ret = FALSE;

		if( isset($response) && !empty( $response ) )
		{
			$results 		= explode("\n", trim($response));

			$resultsFilter 	= array_filter($results, function($value)
			{
				return str_contains($value, ':');
			});

			if(!EMPTY($resultsFilter))
			{

				foreach($resultsFilter as $line)
				{
					[$hashSuffix, $count] = explode(':', $line);

					$check_arr[] = $hashPrefix.$hashSuffix == $hash && $count > intval($this->threshold);
				}

				$ret = !in_array(TRUE, $check_arr);
			}
		}
		
		return $ret;
	}

	protected function makeRequest($hashPrefix)
	{
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, "https://api.pwnedpasswords.com/range/".$hashPrefix);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($curl);

		curl_close($curl);

		return $response;
	}

	 /**
     * Get the hash and its first 5 chars.
     *
     * @param  string  $value
     * @return array
     */
    protected function getHash($value)
    {
        $hash = strtoupper(sha1((string) $value));

        $hashPrefix = substr($hash, 0, 5);

        return [$hash, $hashPrefix];
    }
}