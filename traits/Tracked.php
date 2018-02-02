<?php

use yii\db\ActiveRecord;

/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 20.09.17
 * Time: 15:44
 *
 * @var ActiveRecord $this
 */

namespace app\traits;

trait Tracked
{

    public $trackedAttributes = [];
    public $trackedMessage = null;

    /**
     * Diff model attributes
     *
     * @param array $changedAttributes
     * @param array $oldAttributes
     * @param array|null $lookupAttributes
     * @param array|null $appendedAttributes
     *
     * @return null|string
     */
    public function diff($changedAttributes, $oldAttributes, $lookupAttributes = null, $appendedAttributes = null)
    {

        if(sizeof($this->attributesLookup($lookupAttributes)) == 0)
            return $this->trackedMessage;

        foreach($changedAttributes as $key=>$attribute) {
            if(in_array($key, $this->attributesLookup($lookupAttributes)) && $oldAttributes[$key] != $attribute && !is_object($attribute) && !is_object($oldAttributes[$key])) {
                $this->trackedAttributes[$key] = ['old'=>$oldAttributes[$key], 'new'=>$attribute];
            }
        }

        if(sizeof($this->trackedAttributes) > 0) {

            $this->trackedMessage = '<ul>';

            if(is_array($appendedAttributes)) {
                foreach($appendedAttributes as $key=>$attribute) {
                    $this->trackedMessage .= '<li><strong>'.$key.'</strong>: '.$attribute.'</li>';
                }
            }

            foreach($this->trackedAttributes as $key=>$attribute) {
                $this->trackedMessage .= '<li><strong>'.$this->getAttributeLabel($key). '</strong>:  <s>'.$this->attributesRelation($key, $attribute['new']).'</s> <b>'.$this->attributesRelation($key, $attribute['old']).'</b></li>';
            }

            $this->trackedMessage .= '</ul>';

        }

        return $this->trackedMessage;
    }

    /**
     * Get tracked attributes list
     *
     * @param array|null $lookupAttributes
     *
     * @return array
     */
    abstract public function attributesLookup($lookupAttributes = null);

    /**
     * Get attribute related value
     *
     * @param $attribute
     * @param int|string|null $value
     *
     * @return mixed
     */
    abstract public function attributesRelation($attribute, $value = null);
}