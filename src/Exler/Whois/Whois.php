<?php

namespace Exler\Whois;

use Exler\Whois\Exceptions\ConnectionException;
use Exler\Whois\Exceptions\ParseException;

class Whois
{
    private $timeout;
    private $servers = [];

    /** 
     * @param int $timeout
     */
    public function __construct(int $timeout = 10)
    {
        $this->timeout = $timeout;
        $this->servers = Config::load("servers");
    }

    /** 
     * Lookups WHOIS data for a given domain and returns it.
     * @param string $domain
     * @return string
     */
    public function lookup(string $domain): string
    {
        $server = $this->getWhoisServer($domain);
        $response = $this->sendWhoisQuery($server, $domain);
        return $response;
    }

    /** 
     * Returns a boolean indicating if the given domain is already registered or not.
     * @param string $domain
     * @return bool
     */
    public function isAvailable(string $domain): bool
    {
        return false;
    }

    /** 
     * Returns the TLD of the given domain (with the dot).
     * @param string $domain
     * @return string
     */
    public function getTLD(string $domain): string
    {
        $index = strrpos($domain, ".");
        if ($index === false)
            throw new ParseException("Cannot get TLD of domain");

        return substr($domain, $index);
    }

    /** 
     * Gets the WHOIS server for given domain
     * @param string $domain
     * @return string
     */
    public function getWhoisServer(string $domain): string
    {
        $tld = $this->getTLD($domain);
        if (!isset($this->servers[$tld]))
            $server = $this->getWhoisIANA($domain);
        else
            $server = $this->servers[$tld]["host"];

        return $server;
    }

    /** 
     * Gets the WHOIS server for given domain using IANA WHOIS service
     * @param string $domain
     * @return string
     */
    private function getWhoisIANA(string $domain): string
    {
        $response = $this->sendWhoisQuery("whois.iana.org", $domain);
        $refer = explode("\n", $response)[4];
        $refer = str_replace(" ", "", $refer);

        if (strpos($refer, "whois") === false)
            throw new ConnectionException("No available WHOIS host for this TLD");

        $refer = substr($refer, strpos($refer, "whois"));
        return $refer;
    }

    /** 
     * Sends a given $domain to a $whois server and returns the response.
     * @param string $whois
     * @param string $domain
     * @return string
     */
    private function sendWhoisQuery(string $whois, string $domain): string
    {
        $errno = null;
        $errstr = null;
        $handle = fsockopen($whois, 43, $errno, $errstr, $this->timeout);
        if (!$handle)
            throw new ConnectionException($errstr, $errno);

        $query = $domain . "\r\n";
        if (fwrite($handle, $query) === false)
            throw new ConnectionException("Query cannot be written");

        $response = "";
        while (!feof($handle)) {
            $chunk = fread($handle, 8192);
            if (false === $chunk) {
                throw new ConnectionException("Response chunk cannot be read");
            }
            $response .= $chunk;
        }
        fclose($handle);
        return $response;
    }
}
