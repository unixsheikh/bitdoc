<?php
/**
 * This is a class deals with the basic parsing stuff.
 *
 * @package Bitdoc
 */
namespace Bitdoc;

class PreParser
{
    /**
     * Pre-parse the document.
     *
     * The document gets pre-parsed here. We extract the options from the Yaml.
     *
     * @param  string $file The name of the file.
     *
     * @return array $content An array containing the extracted data.
     */
    public function preParse($file)
    {
        $documentData = file_get_contents($file);

        // Extract the document Yaml options.
        // We only want the very first one. A document might contain Markdown
        // examples, we don't want any Markdown comments hence we use the
        // none-greedy option "?".
        $optionsPattern = '#<!---(.*?)--->#s';

        // preg_match returns 1 if the pattern matches given subject, 0 if it
        // does not, or FALSE if an error occurred.
        if (preg_match($optionsPattern, $documentData, $optionsData)) {

            $optionsData = preg_replace('#<!---#', '', $optionsData[0]);
            $optionsData = preg_replace('#--->#', '', $optionsData);

            // Clean up the very first newline after the Markdown opening comment.
            $optionsData = preg_replace('#(^\n)#', '', $optionsData);

            // Let's replace all newlines with a ":".
            $optionsData = preg_replace('#(?!^\n?)\n(?!\n{0}$)#', ':', $optionsData);

            // Let's remove newlines before and after ":".
            $optionsData = preg_replace('#: #', ':', $optionsData);
            $optionsData = preg_replace('# :#', ':', $optionsData);

            // Create a new numbered array with all the values.
            $optionsData = explode(':', $optionsData);

            // Lets split the array up into a new array in the form key -> value.
            $optionsDataCounter = count($optionsData);
            for ($i=0; $i < $optionsDataCounter; $i+=2) {
                $content[$optionsData[$i]] = $optionsData[$i+1];

                // For easy debugging, if needed.
                //echo $optionsData[$i].' === '.$optionsData[$i+1]."\n";

            }

            // Now we add the original document data as a part of the array, but
            // we clean it up first and remove all the options stuff.
            $content['data'] = preg_replace($optionsPattern, '', $documentData);

            return $content;
        }

        // The Markdown document didn't contain any Yaml options, we just return
        // document as is.
        $content['data'] = $documentData;
        return $content;
    }
}
