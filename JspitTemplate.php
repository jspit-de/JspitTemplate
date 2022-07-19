<?php
//namespace Jspit;
/**
	JspitTemplate.php
.---------------------------------------------------------------------------.
|  Software: JspitTemplate - Simple PHP Template Class                      |
|   Version: 1.5                                                            |
|      Date: 2022-07-19                                                     |
|  PHPVersion >= 7.0                                                        |
| ------------------------------------------------------------------------- |
| Copyright © 2020 - 2021, Peter Junk (alias jspit). All Rights Reserved.   |
| ------------------------------------------------------------------------- |
|   License: Distributed under the Lesser General Public License (LGPL)     |
|            http://www.gnu.org/copyleft/lesser.html                        |
| This program is distributed in the hope that it will be useful - WITHOUT  |
| ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or     |
| FITNESS FOR A PARTICULAR PURPOSE.                                         |
'---------------------------------------------------------------------------'
 */

class JspitTemplate {

  protected static $templatePath = "" ;
  protected static $staticUserFct = [];
  private $tpl = "";  //Template Content
  private $assign = [];
  private $userFct = [];

  /*
   * @param $tplFileName Template-Filename or Template-String
   * @param $flagAsString true if $tplFileName is a template-string, default false
   * @throws Exception if Template-File is not readable
   */
  public function __construct(string $tplFileName = NULL, bool $flagAsString = false) 
  {
    if($tplFileName != NULL){
      if($flagAsString) {
        $this->loadString($tplFileName);
      }
      else {
        $this->loadFile($tplFileName);
      }
      $this->userFct = self::$staticUserFct;
    }
  }

  /*
   * create a object JspitTemplate
   * @param $tplFileName Template-Filename
   * @return object or throw a exeption if error
   */
  public static function create(string $tplFileName) : self
  {
    try{
      $tpl = new self($tplFileName);
    }
    catch (\Exception $e) {
      throw new \InvalidArgumentException('Error read Templatefile "'.$tplFileName.'"');
    }
    return $tpl;
  }
  
  /*
   * createFromString
   * create a object JspitTemplate from a Template-String
   * @param $string a Template
   * @return object self
   */
  public static function createFromString(string $string = "") : self
  {
    return new self($string, true);
  }

  /*
   * renderFromString
   * render a Template-String
   * @param $string a Template
   * @param array $keyValue 
   * @return string HTML
   */
  public static function renderFromString(string $string = "",array $assign = []) : string
  {
    return (new self($string, true))->render($assign);
  }


 /*
  * set default template path
  * @param string $path, without $path or "" reset template path
  * @return realpath if $path exists or false if not
  */
  public static function setTemplatePath(string $path = '') : string
  {
    if($path != ''){
      $path = str_replace('\\','/',$path);
      if(substr($path,-1) != '/') $path .= '/';
    }
    self::$templatePath = $path;
    return $path;
  }

/*
 * render template and get html
 * @param string filename (optional)
 * @param array $keyValue 
 * @return string HTML
 * @throw Exception if arguments incorrect
 */
  public function render(...$par)
  {
    if(isset($par[0])){
      if(is_string($par[0])){
        $this->loadFile(array_shift($par));
      }
      if(is_array($par[0])) {
        return $this->getHTMl($par[0]);
      }
      else {
        throw new \InvalidArgumentException("Error Arguments render");
      } 
    }
    else {
      return $this->getHTMl();
    } 
  }

 /*
  * render code and get HTML
  * @param array $keyValue 
  * @return string HTML
  */ 
  public function getHTML(array $assign = []) : string
  { 
    if(!empty($assign)) $this->assignOnce($assign);
    $this->assignOnce($this->assign);
    $this->replaceDefaults();
    return $this->tpl;
  }

 /*
  * magic function, please refer getHTML
  * @return string HTML
  */
  public function __toString() : string
  {
    return $this->getHTML();
  }

 /*
  * load Template
  * @param String $tplFileName : Filename 
  * @return $this
  * @throw Exception if Template-FileName incorrect
  */
  public function loadFile(string $tplFileName) : self
  {
    if(!preg_match('~^/|^\\\\|^[A-Z]:~',$tplFileName)) {
      //relative Path
      $tplFileName = self::$templatePath.$tplFileName;
    }
    if(!is_readable($tplFileName)) {
      throw new \Exception("Error: Template-File '".$tplFileName."' is not readable");
    }
    $this->tpl = file_get_contents($tplFileName);
    $this->prepare();
    return $this;
  }

 /*
  * load String
  * @param string Template
  * @return $this
  */
  public function loadString(string $template) : self
  {
    $this->tpl = $template;
    //check and prepare
    $this->prepare();
    return $this;
  }


 /*
  * get Template
  * @return string Template 
  */
  public function getTemplate() : string
  {
    return $this->tpl;
  }


 /*
  * save Template
  * @param string $tplFileName
  * @return $this object 
  */
  public function save(string $tplFileName) : self
  {
    if(!preg_match('~^/|^\\\\|^[A-Z]:~',$tplFileName)) {
      //relative Path
      $tplFileName = self::$templatePath.$tplFileName;
    }
    file_put_contents($tplFileName, $this->tpl);
    return $this;
  }

 /*
  * assign : Assign content to placeholders 
  * @param $keyValue array, object or null (default)
  * @return $this object 
  */
  public function assign($keyValue = NULL) : self 
  {
    if(is_object($keyValue) or is_array($keyValue)) {
      $set = (array)$keyValue;
      $this->assign = array_replace_recursive($this->assign, $set);
    }
    elseif($keyValue === NULL){
      //delete all assigns
      $this->assign = [];
    }
    else {
      //error
      $msg = "Invalid first parameter for ".__METHOD__;
      throw new \InvalidArgumentException($msg);
    }
    return $this;
  }

 /*
  * assignOnce : Assign content to placeholders at once
  * @param $keyValue array or object
  * @param bool $insert, default false, true insert before the placeholder
  * @return $this object 
  */
  public function assignOnce($keyValue, bool $insert = false) : self 
  {
    if(is_object($keyValue) or is_array($keyValue)) {
      $set = (array)$keyValue;
    }
    else {
      //error
      $msg = "Invalid first parameter for ".__METHOD__;
      throw new \InvalidArgumentException($msg);
    }
    foreach($set as $key => $val){
      if($val instanceOf self){
        $tpl = $val->assignOnce($val->assign)->getTemplate();
        $this->replaceplaceholder($key, $tpl);
      }
      else {
        foreach($this->getPlaceholders($key) as $tplPlaceholder){
          $fraction = self::splitPlaceholder($tplPlaceholder);
          if($fraction === false OR $val === []) continue;
          $att = $fraction['name'][1] ?? null;
          if($att !== null){  //array or object
            if(is_array($val) AND isset($val[$att])) $newVal = $val[$att];
            elseif(is_object($val) AND isset($val->$att)) $newVal = $val->$att;
            else continue; 
          }
          else {
            $newVal = $val;
          }
          //filters
          if($newVal !== NULL){
            $newVal = $this->handleFilter($fraction['filter'], $newVal);
            //replace
            if($insert) $newVal .= $tplPlaceholder;
            $this->tpl = str_replace($tplPlaceholder, $newVal, $this->tpl);
          }  
        }
      }
    }
    return $this;
  }

 /*
  * getPlaceholders : use internal and for test
  * @param $name name or "" for all
  * @return array("{{foo|format(\"%3d\")}}",..)
  */
  public function getPlaceholders(string $name = "") : array
  {
    $search = $name !== ""  
      ? (' ?'.preg_quote($name,'~').'([. |?][^}]*)?')
      : ('[^}]+')
    ;
    $regEx = '~\{\{'.$search.'\}\}~u';
    preg_match_all($regEx, $this->tpl, $matches);
    return array_unique($matches[0]);
  }

 /*
  * add a userfuction (closure)
  * @param string $name
  * @param $function : closure
  * @return true
  */
  public static function addStaticUserFunction(string $name, $function) : bool
  {
    self::$staticUserFct[$name] = $function;
    return true;
  }

 /*
  * add a userfuction (closure)
  * @param string $name
  * @param $function : closure
  * @return $this
  */
  public function addUserFunction(string $name, $function) : self
  {
    $this->userFct[$name] = $function;
    return $this;
  }


 /*
  * private
  */

 /*
  * check and prepare template
  */
  private function prepare() : void
  {
    $regEx = '/(?<!\\\\)([\'"])(?:(?!(?:\1|\\\\)).|\\\\.)*+\1(*SKIP)(*FAIL)|\h+/m'; 
    $regExCheck = '~^\{\{\w+(\.\w+)?(.+)?\}\}$~';
    foreach($this->getPlaceholders() as $placeHolder){
      $compress = preg_replace($regEx, '', $placeHolder);
      if(!preg_match($regExCheck, $compress)){
        //format error placeholder
        throw new \Exception("Format Error Template Placeholder '".$placeHolder."'");
      }
      $this->tpl = str_replace($placeHolder, $compress,$this->tpl);
    }
  }

 /*
  * replace all placeholders in the template with the value
  */
  private function replaceplaceholder(string $placveholder, string $value)
  {
    $placveholder = preg_quote($placveholder,'~');
    $regEx = '~\{\{ ?'.$placveholder.' ?([|?][^}]+)?\}\}~u';
    $this->tpl = preg_replace($regEx,$value, $this->tpl);
  }

 /*
  * split placeholder in komponents name, filter, default
  * @param $placeholder string e.g.'{{foo.bar|format("%3d")|html??"default val"}}'
  * @return array('name' => .., 'filter' => .., 'default' => ..) or false if error
  */ 
  public static function splitPlaceholder(string $placeholder) 
  {
    $regEx = '~^(?<name>[\w.]+)(?<filter>\|.+?)?(?<default>\?\?.+?)?$~';
    if(!preg_match($regEx, trim($placeholder,'{}'), $match)) return false;
    $fractions = [
      'name' => explode('.', $match[1]),
      'filter' => isset($match[2])
        ? explode('|',trim($match[2],'|'))
        : [],
      'default' => isset($match[3]) 
        ? self::stripQuotes(substr($match[3],2)) 
        : null 
    ];
    return $fractions;
  }

 /*
  * 'abc' => abc, "abc" => abc, 'abc" => 'abc"
  */
  public static function stripQuotes(string $str) : string
  {
    return preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $str);
  }

 /*
  * replace all placeholders in the template with eascaping values
  */
  private function replaceDefaults()
  {
    $regEx = '~\{\{[^?{}]+\?\?([\'"])(.*?)(\1)\}\}~u';
    $this->tpl = preg_replace_callback(
      $regEx,
      function(array $matches){
        return htmlspecialchars($matches[2], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      },
      $this->tpl
    );
  }

 /*
  * handle filter
  * @param string $filter : array with filter strings 
  * 'filter' from placeholder e.g.  ['format("%3d")','html'] 
  * @param mixed $value
  * @return new $value
  */
  private function handleFilter(array $filter, $value) : string
  {
    $esc = true; //optional esc if not html and not url 
    foreach($filter as $fkt){
      if($fkt == "") continue;  
      //split name as $match[1] + argument as $match[2]
      $r =preg_match('~^(\w+)(?:\((.*)\))?$~', $fkt, $match);
      if($r === 0) continue;
      $filter = $match[1]; //filtername
      $arg2 = isset($match[2]) ? trim($match[2],'\'"') : false;
      if($filter == 'html' OR $filter == 'raw') $esc = false;
      elseif(substr($filter,0,3) == 'url') {
        if(is_array($value)) {
          $value = http_build_query($value);
        }
        else {
          $value = rawurlencode($value);
        }
      }
      elseif($filter == 'format' AND $arg2 !== false){
        $value = is_array($value) ? vsprintf($arg2, $value) : sprintf($arg2, $value);
      }
      elseif($filter == 'date' AND $arg2 !== false){
        if(is_numeric($value)) { //timestamp
          $tz = new \DateTimeZone(date_default_timezone_get());
          $value = date_create('@'.$value)->setTimeZone($tz);
        }
        elseif(is_string($value) AND ($dt = date_create($value)) !== false){
          $value = $dt;
        }
        if($value instanceOf \DateTimeInterface) $value = $value->format($arg2);
      }
      elseif(in_array($filter,['selected','checked'])){
        if( $arg2 !== false){
          $value = $value === $arg2 ? $filter : '';
        }
        else {
          $value = $filter; 
        }
      }
      elseif($filter == 'blank') $value = '';
      elseif($filter == 'set') {
        $value = $value !== "" ? (string)$arg2 : "";
      }
      elseif(($filter == 'case' OR $filter == 'sign') AND $arg2 !== false AND $arg2 != ""){
        $delim = substr($arg2,0,1);
        $cases = explode($delim, substr($arg2,1));
        $value = $filter == 'case' 
          ? intval($value)
          : ($value <=> 0) +1  
        ;
        $value = array_key_exists($value, $cases) 
          ? $cases[$value]
          : ""
        ;
      }
      elseif($filter == 'abs' AND is_numeric($value)) {
        $value = abs($value);
      }
      elseif($filter == 'each') {
        $value = $this->filterEach($value,$arg2);
      }
      elseif(array_key_exists($filter, $this->userFct)){
        $value = ($arg2 !== false AND $arg2 !== "") 
          ? $this->userFct[$filter]($value,$arg2)
          : $this->userFct[$filter]($value)
        ;
      }
    }   
    return $esc 
      ? htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') 
      : (string)$value
    ;
  }
  
 /*
  * for each in $value 
  * @param $value (array)
  * @param string $arg  with placeholder for #key# and #val#
  * @param bool $esc escape keys and values
  * @return string new value
  */
  private function filterEach($value,$arg,$esc = true)
  {
    if(is_array($value)){
      $newValue = "";
      foreach($value as $key => $el){
        if($esc){
          $key = htmlspecialchars((string)$key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
          $el = htmlspecialchars((string)$el, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); 
          $newValue .= strtr($arg, ['#key#' => $key, '#val#' => $el])."\r\n"; 
        }
      }
      return trim($newValue);
    }
    //error
    $msg = "Filter each requires an array" ;
    throw new \Exception($msg);
  }

}
