<?php 
namespace Drupal\json_migrate\Plugin\migrate\source; 
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase; 
use Drupal\migrate\Row; 
 
/** 
* Source plugin to import data from JSON files 
* @MigrateSource( 
*   id = "json_page"
* ) 
*/ 
class JsonPage extends SourcePluginBase { 
 
  public function prepareRow(Row $row) { 
    $title = $row->getSourceProperty('city');  
    // some sanitation 
    if (strlen($title) > 255) { 
      $row->setSourceProperty('title', substr($title,0,255)); 
    }  
    return parent::prepareRow($row); 
  } 
 
  public function getIds() { 
    $ids = [ 
      'json_filename' => [ 
        'type' => 'string' 
      ] 
    ]; 
    return $ids; 
  } 
 
  public function fields() { 
    return array( 
      'title' => $this->t('city'),
      'id' => $this->t('_id'), 
      'population' => $this->t('pop'), 
      'state' => $this->t('state'),
    ); 
  } 
 
  public function __toString() {
    return "json data"; 
  } 
 
  /** 
   * Initializes the iterator with the source data. 
   * @return \Iterator 
   *   An iterator containing the data for this source. 
   */ 
  protected function initializeIterator() { 
 
    // loop through the source files and find anything with a .json extension
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('json_migrate')->getPath();
    $path = $module_path . "/cities.json"; 
    $filenames = glob($path); 
    $rows = []; 
    foreach ($filenames as $filename) { 
      // using second argument of TRUE here because migrate needs the data to be 
      // associative arrays and not stdClass objects. 
      $row = json_decode(file_get_contents($filename), true); // sets the title, body, etc. 
      $row['json_filename'] = $filename;
 
      // migrate needs the date as a UNIX timestamp 
      try { 
        // put your source data's time zone here, or just use strtotime() if it's already in UTC 
        $d = new \DateTime($date, new \DateTimeZone('America/Los_Angeles')); 
         $row['date'] = $d->format('U'); 
      } catch (\Exception $e) { 
        echo "Exception: " . $e->getMessage() . "\n"; 
        $row['date'] = time();  // fallback – set it to now so we don't have errors 
      } 
 
      // append it to the array of rows we can import 
      $rows[] = $row;
    } 
 
    // Migrate needs an Iterator class, not just an array
    return new \ArrayIterator($rows);
  }

  // How to create entity type when it does not already exist?
}