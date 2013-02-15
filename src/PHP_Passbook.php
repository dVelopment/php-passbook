<?php
/**
 * @author Daniel Holzmann <d@velopment.at>
 * Date: 15.02.13
 * Time: 13:13
 *
 */
require_once dirname(__FILE__).'/../vendor/PHP-PKPass/PKPass.php';

class PHP_Passbook extends PKPass
{
    /**
     * array with localization informations
     *
     * @var array $locales
     */
    protected $locales = array();

    /**
     * @param string $certPath
     * @param string $certPass
     * @param string $JSON
     * @param array $locales
     */
    public function __construct($certPath = '', $certPass = '', $JSON = '', array $locales = array())
    {
        parent::__construct($certPath, $certPass, $JSON);

        foreach ($locales as $locale=>$strings) {
            $this->addLocale($locale, $strings);
        }
    }

    /**
     * add a locale
     *
     * @param string $locale - the name of the locale (e.g. 'en')
     * @param array $strings - the values in form 'key' => 'value'
     */
    public function addLocale($locale, array $strings)
    {
        $locale = strtolower($locale);
        if (!array_key_exists($locale, $this->locales)) {
            // create newly
            $this->locales[$locale] = $strings;
        } else {
            // append/update
            foreach ($strings as $key=>$value) {
                $this->locales[$locale][$key] = $value;
            }
        }
    }

    /**
     * add a single localized string
     *
     * @param string $locale - the locale (e.g. 'en')
     * @param string $key - the key
     * @param string $value - the localized string
     */
    public function addLocalizedString($locale, $key, $value)
    {
        $this->addLocale($locale, array($key => $value));
    }


    protected function createManifest()
    {
        // we only need to add the localization files to the $this->files array
        // after that we can simply call the parent implementation
        $this->writeLocales();

        return parent::createManifest();
    }

    protected function writeLocales()
    {
        // ensure we have a temporary folder to work in
        $this->paths();

        // write locale file
        foreach ($this->locales as $locale => $strings) {
            $logicalName = sprintf("%s.lproj/pass.strings", $locale);
            $fileName = sprintf("locale_%es.strings", $locale);
            $filePath = sprintf("%s%s/%s", $this->tempPath, $this->uniqid, $fileName);

            // write strings to file
            $fp = fopen($filePath, 'w');
            foreach ($strings as $key=>$value) {
                fwrite($fp, sprintf("\"%s\" = \"%s\"\n", $key, $value));
            }
            fclose($fp);

            // add file to files array
            $this->addFile($filePath, $logicalName);
        }
    }
}
