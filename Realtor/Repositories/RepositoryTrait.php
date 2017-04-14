<?php
namespace Realtor\Repositories;

use InvalidArgumentException;
use Realtor\Models\Http\Stream;
use Realtor\Controllers\PropertiesController;

abstract class RepositoryTrait
{
    private $items;
    private $itemModel;
    private $filePath;

    public function __construct($filePath, array $itemModel)
    {
        if (!is_string($filePath) || empty($filePath)) {
            throw new InvalidArgumentException('Argument $filePath must be a non-empty string.');
        }

        if (empty($itemModel)) {
            throw new InvalidArgumentException('Argument $itemModel must contain an array of the item model.');
        }

        $file = new Stream(fopen($filePath, 'c+'));
        $items = (string) $file;
        $file->close();

        $this->items = empty($items) ? array() : json_decode($items, true);
        $this->itemModel = $itemModel;
        $this->filePath = $filePath;
    }

    public function get($id)
    {
        return isset($this->items[$id]) ? $this->items[$id] : null;
    }

    public function getAll()
    {
        return empty($this->items) ? array() : $this->items;
    }

    public function create(array $item = array(), $id = null, $map = null)
    {
        foreach($this->itemModel as $key => $regex) {
            if (empty($item[$key])) {
                return false;
            }

            if ($regex == null) {
                continue;
            }

            if (preg_match($regex, $item[$key]) !== 1) {
                return false;
            }
        }

        if ($map !== null) {
            $address = array(
                $item['roadname'],
                $item['roadnumber'],
                $item['postalcode'],
                $item['municipality']
            );

            $item['map'] = 'https://www.google.com/maps/embed/v1/place?key='.PropertiesController::MAPS_KEY.'&q='.urlencode(implode($address, ' '));
        }

        if ($id === null) {
            $this->items[self::createUniqId()] = $item;
        } else {
            $itemId = $item[$id];

            if (isset($this->items[$itemId])) {
                return null;
            }
            
            $this->items[$itemId] = $item;
        }
        
        $this->saveFile();
        return true;
    }

    public function update($id, array $item = array())
    {
        foreach($this->itemModel as $key) {
            if (empty($item[$key])) {
                return false;
            }
        }

        if (!isset($this->items[$id])) {
            return false;
        }

        $this->items[$id] = $item;
        $this->saveFile();
        return true;
    }

    public function delete($id)
    {
        if (!isset($this->items[$id])) {
            return false;
        }

        unset($this->items[$id]);
        $this->saveFile();
        return true;
    }

    private function saveFile() 
    {
        $file = new Stream(fopen($this->filePath, 'w+'));
        $file->write(
            json_encode($this->items, JSON_PRETTY_PRINT)
        );
        $file->close();
    }

    private static function createUniqId()
    {
        return md5(uniqid(rand(), true));
    }
}