<?php

namespace StageLogboek;

use Curl\Curl;
use DateTime;

/**
 * Created by PhpStorm.
 * User: Dieter
 * Date: 8/03/2017
 * Time: 10:47
 */
class Session
{
    private $baseURL = 'https://internship.ehb.be/Multec/nl';

    private $username;
    private $password;

    private $authCookie;
    private $internshipID;

    /**
     * StageLogboekSession constructor.
     * @param $username
     * @param $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /*
     * GETTERS
     */

    /**
     * @return mixed
     */
    public function getInternshipID()
    {
        if (!isset($this->internshipID))
            $this->internshipID = Parser::findInternshipID($this->get("Internships/ByStudent"));

        return $this->internshipID;
    }

    /**
     * @return mixed
     */
    public function getAuthCookie()
    {
        return $this->authCookie;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /*
     * ACTIONS
     */

    private function get($url, $data = [])
    {
        $curl = new Curl();
        $this->setOpt($curl);

        // Load
        $curl->get("{$this->baseURL}/$url", $data);

        return $curl->response;
    }

    private function post($url, $data = [], $cookies = [])
    {
        $curl = new Curl();
        $this->setOpt($curl, $cookies);

        // Load
        $curl->post("{$this->baseURL}/$url", $data);

        return $curl->response;
    }

    /**
     * @return string
     */
    private function login()
    {
        $curl = new Curl();
        $this->setOpt($curl);

        $curl->post("{$this->baseURL}/Account/Login", [
            "__RequestVerificationToken" => Parser::findTokenFromInput($this->get("Account/Login")),
            "UserName" => $this->getUsername(),
            "Password" => $this->getPassword(),
            "RememberMe" => 'true',
        ]);

        // Save cookies
        $cookies = Parser::parseCookies($curl->response_headers);
        if (isset($cookies['.ASPXAUTH'])) {
            $this->authCookie = $cookies['.ASPXAUTH'];
        }

        return $curl->response;
    }

    /**
     * @param Entry $entry
     * @return string
     */
    function addEntry(Entry $entry)
    {
        if (!$this->isLoggedIn()) {
            $this->login();
        }

        // Get RequestVerificationToken
        /*
        $curl = new Curl();
        $this->setOpt($curl);
        $curl->get("{$this->baseURL}/LogBook/Edit?internshipId={$this->internshipID}");
        $cookie = Parser::findTokenFromCookie($curl->response_headers);
        */

        // Build query parameter
        $params = [
            'internshipId' => $this->getInternshipID(),
            'Id' => '0',
            'IshipLogEntry_Internship' => $this->getInternshipID(),
            'Description' => $entry->getDescription(),
            'Day' => $entry->getStart()->format('Y-m-d'),
            'StartHr' => $entry->getStart()->format('H'),
            'StartMin' => $entry->getStart()->format('i'),
            'EndHr' => $entry->getEnd()->format('H'),
            'EndMin' => $entry->getEnd()->format('i'),
            'ishipQuestions' => $entry->getCompetences(),
        ];
        $data = http_build_query($params);
        $data = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $data);

        // POST
        return $this->post("LogBook/Edit", $data);
    }

    /**
     * @param Entry $entry
     * @return string
     */
    function removeEntry(Entry $entry)
    {
        if (!$this->isLoggedIn()) {
            $this->login();
        }

        return $this->get("LogBook/Delete", [
            'day' => $entry->getStart()->format('Y-m-d h:i:s'),
            'internshipId' => $this->getInternshipID(),
        ]);
    }

    /**
     * @param Entry[] $entries
     */
    public function addEntries($entries)
    {
        if (is_array($entries) && !empty($entries)) {
            foreach ($entries as $entry) {
                $this->addEntry($entry);
            }
        }
    }

    /**
     * @return Entry[]
     */
    public function getEntries()
    {
        if (!$this->isLoggedIn()) {
            $this->login();
        }

        // Find days with entries
        $response = $this->get("LogBook", [
            'internshipId' => $this->getInternshipID(),
        ]);

        // Parse overview page
        $days = Parser::parseOverview($response);

        // Parse day pages
        $entries = [];

        foreach ($days as $day => $editURL) {
            parse_str($editURL, $query);

            if (isset($query['day']) && $date = DateTime::createFromFormat('m/d/Y H:i:s', $query['day'])) {
                $entries[$day] = $this->getEntriesForDate($date);
            }
        }

        return $entries;
    }

    public function getEntriesForDate(DateTime $date)
    {
        if (!$this->isLoggedIn()) {
            $this->login();
        }

        $response = $this->get("LogBook/Edit", [
            'internshipId' => $this->getInternshipID(),
            'day' => $date->format('m-d-Y h:i:s'),
        ]);

        return Parser::parseDay($date->format('d/m/Y'), $response);
    }

    /*
     * HELPER
     */

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return isset($this->cookies) && isset($this->token);
    }

    /**
     * @param Curl $curl
     * @param array $cookies
     */
    private function setOpt(Curl &$curl, $cookies = [])
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 5.1; rv:18.0) Gecko/20100101 Firefox/18.0',
            'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko'
        ];

        $curl->setUserAgent($userAgents[array_rand($userAgents)]);
        $curl->setOpt(CURLOPT_FOLLOWLOCATION, 1);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);

        if (isset($this->authCookie)) {
            $curl->setCookie('.ASPXAUTH', $this->authCookie);
        }

        if (!empty($cookies)) {
            foreach ($cookies as $key => $value) {
                $curl->setCookie($key, $value);
            }
        }
    }
}