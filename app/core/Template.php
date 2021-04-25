<?php

namespace App\Core;

use Exception;

if( !class_exists('Template') ){
    class Template{

        private $controller;
        private $functions;
        private $values;

        public function __construct($controller){
            $this->controller = $controller;
            $this->functions = array();
        }

        public function render($file, $data = null){
            $template = file_get_contents("app/modules/{$this->controller}/views/{$file}.template.html");

            // rendering variables
            if( $data !== null ){
                $this->values = $data;
                
                // $template = $this->searchForeachs2($template);
                $template = $this->searchForeachs($template);
                $template = $this->searchSignedValues($template);
            }
            
            echo $template;
        }

        private function signValue($file_content, $match, $value){
            $file_content = str_replace($match, $value, $file_content);
            return $file_content;
        }

        private function getValue($indexes, $array){
            if( sizeof($indexes) === 0 ){
                return $array;
            } if( array_key_exists($indexes[0], $array) ){
                if( is_array($array[$indexes[0]]) ){
                    return $this->getValue( array_slice($indexes, 1, sizeof($indexes)), $array[$indexes[0]] );
                } else {
                    return $array[$indexes[0]];
                }
            }
        }

        private function searchSignedValues($file_content){
            $regex = "/\{[-a-z]+\}|\{[a-z]+\[[a-z|0-9]+\].*?\}/";

            $n_matches = preg_match_all($regex, $file_content, $matches);

            foreach($matches[0] as $index => $match){
                $array_sections = str_replace(['{','}',']'], '', $match);
                $array_sections = explode('[', $array_sections);

                $value = $this->getValue($array_sections, $this->values);

                if( !$value ){
                    throw new Exception("The variable {$match} was not passed to the template!", 1);
                }

                $file_content = $this->signValue($file_content, $match, $value);
            }

            return $file_content;
        }

        private function searchForeachs($file_content){
            
            $regex = "/@foreach\(\s?+[a-z|0-9|\W]+\s+as\s+([a-z|0-9]+|[a-z|0-9]+\s+[\W]+\s+[a-z|0-9]+)\s?+\)[A-Z|a-z|0-9|\s|\W]+?@endforeach/";

            $n_matches = preg_match_all($regex, $file_content, $matches);

            foreach($matches[0] as $index => $match){
                $regex_find_command = "/@foreach\(\s?+[a-z|0-9|\W]+\s+as\s+([a-z|0-9]+|[a-z|0-9]+\s+[\W]+\s+[a-z|0-9]+)\s?+\)/";
                preg_match($regex_find_command, $match, $prev_command);
            
                $command = $prev_command[0];
                $command = str_replace(['@foreach', '(',')'], '', $command);

                $array_name = trim(explode(' as ', $command)[0]);
                $array_alias = trim(explode(' as ', $command)[1]);
                
                $key = null;

                $code = $match;
                $regex_extract_code = "/@foreach\(\s?+[a-z|0-9|\W]+\s+as\s+([a-z|0-9]+|[a-z|0-9]+\s+[\W]+\s+[a-z|0-9]+)\s?+\)|@endforeach/";

                $code = trim(preg_replace($regex_extract_code, '', $code));
                $new_code = "";

                if( sizeof(explode(' => ', $array_alias)) === 1 ){
                    foreach( $this->values[$array_name] as $key => $elemet ){
                        $regex_replace_array_names = "/\{".$array_alias."/";
                        $new_code .= trim(preg_replace($regex_replace_array_names, "{".$array_name."[{$key}]", $code));
                    }
                } else {
                    $key = trim(explode(' => ', $array_alias)[0]);
                    $array_alias = trim(explode(' => ', $array_alias)[1]);

                    $array_sections = str_replace(['{','}', ']'], '', $array_name);
                    $array_sections = explode('[', $array_sections);
                    $data = $this->getValue($array_sections, $this->values);

                    foreach( $data as $key => $elemet ){
                        $regex_replace_array_names = "/\{".$array_alias."/";
                        $new_code .= trim(preg_replace($regex_replace_array_names, "{".$array_name."[{$key}]", $code));
                    }
                }
                
                $file_content = str_replace($match, $new_code, $file_content);
            }

            return $file_content;
        }

        private function searchForeachs2($file_content){
            $regex = "/@foreach\(\s?+[a-z|0-9|\W]+\s+as\s+([a-z|0-9]+|[a-z|0-9]+\s+[\W]+\s+[a-z|0-9]+)\s?+\)[A-Z|a-z|0-9|\s|\W]+?@endforeach/";

            $n_matches = preg_match_all($regex, $file_content, $matches);

            var_dump($matches);

            foreach($matches[0] as $index => $match){
                $regex_find_command = "/@foreach\(\s?+[a-z|0-9|\W]+\s+as\s+([a-z|0-9]+|[a-z|0-9]+\s+[\W]+\s+[a-z|0-9]+)\s?+\)/";
                preg_match($regex_find_command, $match, $prev_command);
                
                $command = $prev_command[0];
                $array_name = trim(explode(' as ', str_replace(['@foreach', '(',')'], '', $command))[0]);
                $array_alias = trim(explode(' as ', str_replace(['@foreach', '(',')'], '', $command))[1]);
                var_dump($file_content);
                var_dump($command);
                var_dump($array_name);
                var_dump($array_alias);
                if( sizeof(explode(' => ', $array_alias)) === 1 ){
                    
                } else {
                    $key = trim(explode(' => ', $array_alias)[0]);
                    $array_alias = trim(explode(' => ', $array_alias)[1]);

                }
            }

            
            return $file_content;
        }

        public function register_functions($function){
            if( is_array($function) ){
                $this->functions = array_merge($this->functions, $function);
            } else {
                array_push($this->functions, $function);
            }
        }
    }
}