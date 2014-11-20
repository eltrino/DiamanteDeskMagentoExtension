<?php
/**
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */

class Eltrino_DiamanteDesk_Model_Source_Branch
{
    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => 0,
                'label' => Mage::helper('core/translate')->__('All')
            )
        );

        $branches = Mage::getSingleton('eltrino_diamantedesk/api')->getBranches();

        $branches = json_decode($branches);

        if ($branches && is_array($branches)) {
            foreach ($branches as $branch) {
                $options[] = array(
                    'value' => $branch->id,
                    'label' => $branch->name
                );
            }
        }

        return $options;
    }

    public function toArray()
    {
        $options = $this->toOptionArray();
        $newOptions = array();
        foreach ($options as $option) {
            $newOptions[$option['value']] = $option['label'];
        }
        unset($newOptions[0]);
        return $newOptions;
    }
}