<?php

/**
 * Reader - PHP class to read file and write into csv
 * NOTE: Designed PHP version 5.4
 * @author Ekaba Bisong
 */

class Reader {

// Class variables
    private $_filename = "data.txt";    //hold file name
    private $_newfile = "new_data.txt"; //hold new file name
    private $_i = 1;                    //hold running number value
    private $_data = array();           //hold array of strings to be parsed into csv format
    private $read_fh;                   //reader file handle
    private $write_fh;                  //writer file handle
    private $array;                     //array to store read file contet

    public function init() {
        //if (file_exists($this->_filename) && is_readable($this->_filename)) {
        try {
            //method to open file for reading/ writing - read file contents into array
            $this->openAndReadFile($this->_filename, $this->_newfile);

            //iterate through each line of file
            for ($start = 0; $start < count($this->array); $start++) {
                //print list header
                $this->printListHeader($start);
                //print formatted list
                $this->printList($start);
            }

            $this->closeReadWriteBuffer();
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }

    //function to open file for reading and set printwriter
    private function openAndReadFile($data, $newData) {
        $this->read_fh = fopen($data, "r");
        $this->write_fh = fopen($newData, "w");

        $this->array = file($data);
        
        throw new Exception ("File $data not found");
    }

    //function to print list header
    private function printListHeader($start) {
        //check if first line of Section One but not first line of file
        if ($start == 0) {
            $temp = trim($this->array[$start]);
            //print $temp . "<br/>";
            fwrite($this->write_fh, $temp . "\n");
            $i = 1;
        }

        //check if line is a header of section one
        else if (strpos($this->array[$start], "ITEMS ON RESERVE FOR") !== false && $start > 3) {
            $temp = trim($this->array[$start]);
            //print "<br/><br/>" . $temp . "<br/>";
            fwrite($this->write_fh, "\n\n" . $temp . "\n");
            $i = 1;
        }

        $this->modifyLabels($start);
    }

    //function to modify header labels by removing LECTURER and COURSE
    private function modifyLabels($start) {
        //check if string contains LECTURER and remove word
        if (strpos($this->array[$start], "LECTURER") !== false) {
            $search = "LECTURER";
            $temp = trim(str_replace($search, '', $this->array[$start]));
            //print $temp . "<br/>";
            fwrite($this->write_fh, $temp . "\n");
        }

        //check if string contains COURSE and remove word
        else if (strpos($this->array[$start], "COURSE") !== false) {
            $search = "COURSE";
            $temp = trim(str_replace($search, '', $this->array[$start]));
            //print $temp . "<br/>";
            fwrite($this->write_fh, $temp . "\n");

            //check if last line of Section One
            if (strpos($this->array[$start], "PERMANENT") !== false) {
                //print "<br/>";
                fwrite($this->write_fh, "\n");
            }
        }
    }

    //function to print list in csv style
    private function printList($start) {
        //check if string is blank and remove
        if (!preg_match('([a-zA-Z])', $this->array[$start])) {
            continue;
        } else {
            if (!preg_match('([a-zA-Z0-9])', $this->array[$start])) {
                continue;
            }
            //replace delimeters (i.e. quote, double-quote and colon) with a comma
            $this->array[$start] = trim(str_replace(array('\'', '"', ':'), ',', $this->array[$start]));
            //replace spaces greater than 3 with a comma
            $temp = preg_replace('/   [ ]+/', ',', $this->array[$start]);
            //push three lines into array, then reset array
            if (count($this->_data) == 3) {
                //var_dump($data);
                array_unshift($this->_data, trim($this->_i . ','));
                fputcsv($this->write_fh, $this->_data, ",", "\t");
                $this->_data = array();
                $this->_i++;
            }
            array_push($this->_data, trim($temp));
        }
    }

    //function to close all buffers
    private function closeReadWriteBuffer() {
        fclose($this->read_fh);
        fclose($this->write_fh);
    }

}
?>