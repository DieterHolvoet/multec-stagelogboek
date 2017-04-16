<?php
/**
 * Created by PhpStorm.
 * User: Dieter
 * Date: 2/04/2017
 * Time: 17:35
 */

namespace StageLogboek;

use Artack\DOMQuery\DOMQuery;
use DateTime;
use DOMElement;

class Parser
{

    /**
     * @return array
     */
    private static function getCompetences()
    {
        return [
            "1" => 'Inquisitiveness and interest',
            "3" => 'Analysis and synthesis',
            "4" => 'Application of knowledge and theoretical perception',
            "5" => 'Initiative',
            "6" => 'Effort an perseverance',
            "7" => 'Discipline & accuracy',
            "8" => 'Personal care',
            "9" => 'Flexibility',
            "10" => 'Creativity and innovation',
            "11" => 'Care for people, material and environment',
            "12" => 'Personal planning and organization',
            "13" => 'Quality assurance and practicaity',
            "14" => 'Working method',
            "15" => 'Social demeanour',
            "16" => 'Persuasiveness / assertiveness',
            "17" => 'Communication',
            "18" => 'Teamwork / company integration',
        ];
    }

    /**
     * @param $response
     * @return string
     */
    public static function findTokenFromInput($response)
    {
        preg_match('#<input name="__RequestVerificationToken" type="hidden" value="(.*?)"#is', $response, $match);
        return $token = $match[1];
    }

    /**
     * @param $responseHeaders
     * @return string
     */
    public static function findTokenFromCookie($responseHeaders)
    {
        $cookies = Parser::parseCookies($responseHeaders);
        $cookies = array_filter($cookies, function ($cookieName) {
            return strpos($cookieName, '__RequestVerificationToken') === 0;
        }, ARRAY_FILTER_USE_KEY);

        return empty($cookies) ? false : $cookies;
    }

    /**
     * @param $response
     * @return string
     */
    public static function findInternshipID($response)
    {
        preg_match('/internshipId=([0-9]{4})/', $response, $match);
        return $token = $match[1];
    }

    /**
     * @param bool $response
     * @return string
     */
    function findEntryID($response = false)
    {
        preg_match('/internshipId=([0-9]{4})/', $response, $match);
        return $token = $match[1];
    }

    /**
     * @param $response
     * @return array
     */
    public static function parseDay($day, $response)
    {
        $DOM = DOMQuery::create($response);
        $entries = [];
        $elements = $DOM->find('tr')->filter('.summaryTable');

        /** @var DOMElement $element */
        foreach ($elements as $key => $element) {
            if ($key === 0)
                continue;

            $td = $element->find('td');

            $start = DateTime::createFromFormat('d/m/Y H:i', "{$day} {$td->get(0)->getInnerHtml()}");
            $end = DateTime::createFromFormat('d/m/Y H:i', "{$day} {$td->get(1)->getInnerHtml()}");
            $description = $td->get(3)->find('span')->getFirst()->getInnerHtml();

            $competences = $td->get(4)->find('span')->getFirst()->getInnerHtml();
            $competences = explode(";\n ", trim($competences));
            $competences = array_map(function ($competence) {
                return array_search($competence, self::getCompetences());
            }, $competences);
            $competences = array_values($competences);

            $entries[] = new Entry($start, $end, $description, $competences);
        }

        return $entries;
    }

    /**
     * @param $response
     * @return array
     */
    public static function parseOverview($response)
    {
        $days = [];
        $DOM = DOMQuery::create($response);

        /** @var DOMElement $element */
        foreach ($DOM->find('a') as $element) {
            $href = $element->getAttribute('href');
            $parsed = parse_url($href);
            if (!isset($parsed['query']))
                continue;

            parse_str($parsed['query'], $query);
            if (!isset($query['day']))
                continue;

            $day = DateTime::createFromFormat('m/d/Y H:i:s', $query['day']);

            if (strpos($href, '/Multec/nl/logbook/edit') === 0) {
                $days[$day->format('d/m/Y')] = $href;
            }
        }

        return $days;
    }

    /**
     * @param $responseHeaders
     * @return array
     */
    public static function parseCookies($responseHeaders)
    {
        preg_match_all('|Set-Cookie: (.*)=(.*);|U', implode($responseHeaders), $matches);
        return [$matches[1][0] => $matches[2][0]];
    }

    /**
     * @param $string       The template string
     * @param $variables    The values to replace the placeholders
     * @return mixed        The finished string
     */
    public static function parseTemplate($string, $variables)
    {
        while (preg_match('/{(\[(.*?)\])?(.*?)(\[(.*?)\])?}/', $string, $matches, PREG_OFFSET_CAPTURE)) {
            $startIndex = $matches[0][1];
            $offset = strlen($matches[0][0]);

            $prefix = $matches[2][0];
            $key = $matches[3][0];
            $value = $variables[$key] ?: '';

            if (!empty($value) && !empty($prefix)) {
                $value = $prefix.$value;
            }

            $string = substr_replace($string, $value, $startIndex, $offset);
        }

        return $string;
    }
}