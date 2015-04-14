<?php
/*
 * Copyright 2005-2015 CENTREON
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace Centreon\Internal\Form\Component;

use Centreon\Internal\Di;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Email extends Component
{
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        (isset($element['html']) ? $value = 'value="'.$element['html'].'" ' :  $value = '');
        
        if (!isset($element['label']) || (isset($element['label']) && empty($element['label']))) {
            $element['label'] = $element['name'];
        }
        
        if (!isset($element['placeholder']) || (isset($element['placeholder']) && empty($element['placeholder']))) {
            $placeholder = 'placeholder="'.$element['name'].'" ';
        }
        
        if (!isset($element['id']) || (isset($element['id']) && empty($element['id']))) {
            $element['id'] = $element['name'];
        }
        
        $addClass = '';
        $required = '';
        if (isset($element['label_mandatory']) && $element['label_mandatory'] == "1") {
            $addClass .= 'mandatory-field ';
            $required = ' required';
        }
        
        $myJs = "";
        
        $myHtml = '<div id="'.$element['name'].'_email" class="input-group input-group-sm">
                <span class="input-group-addon">@</span>';
        
        $myHtml .= '<input '.
                'id="'.$element['id'].'" '.
                'type="email" '.
                'name="'.$element['name'].'" '.
                $value.
                'class="form-control '.$addClass.'" '.
                $placeholder.
                $required .
                '/>';
                    
        $myHtml .= '<span id="'.$element['name'].'_email_span" class=""></span>'
            . '</div>';
        
        return array(
            'html' => $myHtml,
            'js' => $myJs
        );
    }
    
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function addValidation($element)
    {
        $validations = parent::addValidation($element);
        
        $validationUrl = Di::getDefault()
                            ->get('router')
                            ->getPathFor('/validator/email');
        
        $validations['eventValidation'] .= ' $("#'.$element['name'].'").on("blur", function() {
                    $.ajax({
                        url: "'.$validationUrl.'",
                        type: "POST",
                        data: {"email":$("#'.$element['name'].'").val()},
                        context: document.body
                    })
                    .success(function(data, status, jqxhr) {
                        if (data.success) {
                            $("#'
                            .$element['name']
                            .'_email").removeClass("has-error has-feedback");
                            $("#'
                            .$element['name']
                            .'_email_span").removeClass("glyphicon glyphicon-remove form-control-feedback");
                            $("#'
                            .$element['name']
                            .'_email").addClass("has-success has-feedback");
                            $("#'
                            .$element['name']
                            .'_email_span").addClass("glyphicon glyphicon-ok form-control-feedback");    
                        } else {
                            $("#'
                            .$element['name']
                            .'_email").removeClass("has-error has-feedback");
                            $("#'
                            .$element['name']
                            .'_email_span").removeClass("glyphicon glyphicon-ok form-control-feedback");
                            $("#'
                            .$element['name']
                            .'_email").addClass("has-error has-feedback");
                            $("#'
                            .$element['name']
                            .'_email_span").addClass("glyphicon glyphicon-remove form-control-feedback"); 
                        }
                    });
                });';
        
        return $validations;
    }
}
