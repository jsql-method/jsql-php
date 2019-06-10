<?php
namespace JSQL;

/**
 * Class Handler
 * @package JSQL
 */
class Handler
{
    /**
     * @var string Transaction id name
     */
    public static $txId = "txid";

    /**
     * Retrieve the post request result
     *
     * @since       1.0.0
     * @param       string      $path       Request endpoint
     * @param       array|null  $headers    Request headers
     * @return      void                    Parsed object
     */
    public static function request_post($path, $headers = NULL)
    {
        // POST content reading
        $json = file_get_contents('php://input');

        // TXID Append
        $postHeaders = getallheaders();
        $txName = self::$txId;
        $txId = $postHeaders[$txName] ?? null;

        // Prepare query string
        $baseHeaders = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json)
	    );

        // If txid exists
        if( $txId )
        {
            $baseHeaders[] = "{$txName}: {$txId}";
        }

        // Custom headers merge
        if(isset($headers))
        {
            // Prepare headers string
            foreach($headers as $header => $value)
            {
                $baseHeaders[] = "{$header}: $value";
            }
        }

        // Curl handler
        $handler = curl_init();
        $responseHeaders = [];
        curl_setopt($handler, CURLOPT_URL, $path);
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $json);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handler, CURLOPT_HTTPHEADER, $baseHeaders);
        curl_setopt($handler, CURLOPT_HEADERFUNCTION,
            function($curl, $header) use (&$responseHeaders)
            {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) // ignore invalid headers
                    return $len;

                $name = strtolower(trim($header[0]));
                if (!array_key_exists($name, $responseHeaders))
                    $responseHeaders[$name] = [trim($header[1])];
                else
                    $responseHeaders[$name][] = trim($header[1]);

                return $len;
            }
        );

        // Receive response object
        $responseBody = curl_exec($handler);

        // Apply headers
        header('Content-Type: application/json');
        if( isset($responseHeaders[self::$txId]) )
        {
            header("{$txName}: {$responseHeaders[self::$txId]}");
        }

        // Print result
        print $responseBody;
    }
}
