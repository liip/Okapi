<?php

/**
 * This class adds the method getDOM() to Zend_Form and provides therefore 
 * the possibility to pass the form object to the XSL view.
 *
 * @see Zend_Form
 */
class api_form extends Zend_Form {

    protected $name = 'form';

    public function __construct($options = null) {
        parent::__construct($options);
        
        $this->setMethod('post'); // default method
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function filter(array $keys, array $add = array()) {
        
        $values = $this->getValues();
        $filtered = array();
        
        foreach ($keys as $key => $value) {
            if (is_string($key)) {
                if (isset($values[$key])) {
                    $filtered[$value] = $values[$key];
                }
            } elseif (isset($values[$value])) {
                $filtered[$value] = $values[$value];
            }
        }
        
        return array_merge($filtered, $add);
    }

    public function getDOM() {
        
        $dom = new DOMDocument();
        $dom->loadXML('<' . $this->name . '/>');
        $root = $dom->documentElement;
        
        // General form stuff
        $action = $dom->createAttribute('action');
        $action->appendChild($dom->createTextNode($this->getAction()));
        $root->appendChild($action);
        
        $enctype = $dom->createAttribute('enctype');
        $enctype->appendChild($dom->createTextNode($this->getEnctype()));
        $root->appendChild($enctype);
        
        $method = $dom->createAttribute('method');
        $method->appendChild($dom->createTextNode($this->getMethod()));
        $root->appendChild($method);
        
        // Loop display group
        foreach ($this->getDisplayGroups() as $group) {
            $groupNode = $dom->createElement('group');
            
            // Disply group name and description 
            $name = $dom->createAttribute('name');
            $name->appendChild($dom->createTextNode($group->getName()));
            $groupNode->appendChild($name);
            
            $description = $dom->createAttribute('description');
            $description->appendChild($dom->createTextNode($group->getDescription()));
            $groupNode->appendChild($description);
            
            // Attribs of display group
            foreach ($group->getAttribs() as $key => $value) {
                if (is_string($value)) {
                    $attrib = $dom->createElement('attrib');
                    $keyNode = $dom->createAttribute('key');
                    $keyNode->appendChild($dom->createTextNode($key));
                    $attrib->appendChild($keyNode);
                    $attrib->appendChild($dom->createTextNode($value));
                    $groupNode->appendChild($attrib);
                }
            }
            
            // And finally the elements
            foreach ($group->getElements() as $element) {
                $node = $dom->createElement('element');
                
                // Element generals
                $label = $dom->createAttribute('label');
                $label->appendChild($dom->createTextNode($element->getLabel()));
                $node->appendChild($label);
                
                $name = $dom->createAttribute('name');
                $name->appendChild($dom->createTextNode($element->getName()));
                $node->appendChild($name);
                
                $id = $dom->createAttribute('id');
                $id->appendChild($dom->createTextNode($element->getId()));
                $node->appendChild($id);
                
                $type = $dom->createAttribute('type');
                $type->appendChild($dom->createTextNode($element->getType()));
                $node->appendChild($type);
                
                $description = $dom->createAttribute('description');
                $description->appendChild($dom->createTextNode($element->getDescription()));
                $node->appendChild($description);
                
                $required = $dom->createAttribute('required');
                $required->appendChild($dom->createTextNode($element->isRequired() ? 'true' : 'false'));
                $node->appendChild($required);
                
                $valid = $dom->createAttribute('error');
                $valid->appendChild($dom->createTextNode($element->hasErrors() ? 'true' : 'false'));
                $node->appendChild($valid);
                
                // Messages for element
                $messages = array();
                foreach ($element->getErrors() as $error) {
                    if (! in_array($error, $messages)) {
                        $messages[] = $error;
                        
                        $message = $dom->createElement('message');
                        $message->appendChild($dom->createTextNode($error));
                        $node->appendChild($message);
                    }
                }
                
                // Attribs of element
                foreach ($element->getAttribs() as $key => $value) {
                    if (is_string($value)) {
                        $attrib = $dom->createElement('attrib');
                        $keyNode = $dom->createAttribute('key');
                        $keyNode->appendChild($dom->createTextNode($key));
                        $attrib->appendChild($keyNode);
                        $attrib->appendChild($dom->createTextNode($value));
                        $node->appendChild($attrib);
                    }
                }
                
                // Element with array type
                $array = $dom->createAttribute('array');
                $array->appendChild($dom->createTextNode($element->isArray() ? 'true' : 'false'));
                $node->appendChild($array);
                
                // Value of element
                if (!$element instanceof Zend_Form_Element_File) {
                    if (is_array($element->getValue())) {
                        foreach ($element->getValue() as $key => $value) {
                            $valueNode = $dom->createElement('value');
                            $keyNode = $dom->createAttribute('key');
                            $keyNode->appendChild($dom->createTextNode($key));
                            $valueNode->appendChild($keyNode);
                            $valueNode->appendChild($dom->createTextNode($value));
                            $node->appendChild($valueNode);
                        }
                    } else {
                        $value = $dom->createAttribute('value');
                        $value->appendChild($dom->createTextNode($element->getValue()));
                        $node->appendChild($value);
                    }
                }
                
                // Special case multi elements
                if ($element instanceof Zend_Form_Element_Multi) {
                    foreach ($element->getMultiOptions() as $key => $value) {
                        $optionNode = $dom->createElement('option');
                        $keyNode = $dom->createAttribute('key');
                        $keyNode->appendChild($dom->createTextNode($key));
                        $optionNode->appendChild($keyNode);
                        $optionNode->appendChild($dom->createTextNode($value));
                        $node->appendChild($optionNode);
                    }
                }
                
                // Special case captcha
                if ($element instanceof Zend_Form_Element_Captcha) {
                    $captcha = $element->getCaptcha();
                    if ($captcha instanceof Zend_Captcha_Image) {
                        $captcha->generate();
                        
                        $img = $dom->createAttribute('img');
                        $img->appendChild($dom->createTextNode($captcha->getImgUrl() . $captcha->getId() . $captcha->getSuffix()));
                        $node->appendChild($img);
                        
                        $captchaNode = $dom->createAttribute('captcha');
                        $captchaNode->appendChild($dom->createTextNode($captcha->getId()));
                        $node->appendChild($captchaNode);
                    }
                }
                
                $groupNode->appendChild($node);
            }
            $root->appendChild($groupNode);
        }
        
        // Return that!
        return $dom;
    }
}
