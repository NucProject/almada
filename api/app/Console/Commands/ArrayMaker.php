<?php
/**
 * Created by PhpStorm.
 * User: healer_kx@163.com
 * Date: 16/12/9
 * Time: 上午8:59
 */

namespace App\Console\Commands;


class ArrayMaker
{
    /**
     * @var array Sorted array
     */
    private $dataFields = [];

    private $result = [];

    public function __construct($dataFields)
    {
        usort($dataFields, function ($a, $b) {
            if ($a['name'] == $b['name']) {
                return 0;
            } elseif ($a['name'] < $b['name']) {
                return -1;
            }
            return 1;
        });

        $this->dataFields = $dataFields;
    }


    public function makeArray()
    {

        foreach ($this->dataFields as $field)
        {
            $fieldNames = explode('.', $field['name']);
            if (count($fieldNames) == 1)
            {
                $fieldName = current($fieldNames);
                $this->setKeyValue($this->result, $fieldName, $field['type'], $field['value']);
            }
            else
            {
                $counter = 0;
                $array = &$this->result;
                foreach ($fieldNames as $fieldName) {
                    $counter += 1;

                    if (!array_key_exists($fieldName, $array))
                    {
                        if ($counter != count($fieldNames)) // Not the last one
                        {
                            $array[$fieldName] = [];
                        }
                        else
                        {
                            $this->setKeyValue($array, $fieldName, $field['type'], $field['value']);
                        }

                    }

                    $array = &$array[$fieldName];

                }

            }
        }

        return $this->result;
    }

    /**
     * @param array $array
     * @param $fieldName
     * @param $fieldType
     * @param string $fieldValue
     */
    private function setKeyValue(array &$array, $fieldName, $fieldType, $fieldValue='')
    {
        if ($fieldType == 'string')
        {
            $array[$fieldName] = $fieldValue;
        }
        elseif ($fieldType == 'int')
        {
            $array[$fieldName] = intval($fieldValue);
        }
        elseif ($fieldType == 'double' || $fieldType == 'float')
        {
            $array[$fieldName] = floatval($fieldValue);
        }
        elseif ($fieldType == 'json')
        {
            $array[$fieldName] = json_decode($fieldValue, true);
        }
    }
}

/* Test code, seems OK
$dataFields = [
    ['name' => 'a', 'comment' => 'A', 'type' => 'int'],
    ['name' => 'b', 'comment' => 'B', 'type' => 'double'],
    ['name' => 'c', 'comment' => 'C', 'type' => 'string'],
    ['name' => 'list.0.a', 'comment' => 'object.a in list', 'type' => 'int'],
    ['name' => 'list.0.b', 'comment' => 'object.b in list', 'type' => 'string'],
    ['name' => 'obj.a', 'comment' => 'object.a', 'type' => 'string'],
    ['name' => 'obj.b', 'comment' => 'object.b', 'type' => 'string'],
    ['name' => 'obj', 'comment' => 'C', 'type' => 'object'],
];

$am = new ArrayMaker($dataFields);
$r = $am->makeArray();

echo json_encode($r, JSON_PRETTY_PRINT);
*/
