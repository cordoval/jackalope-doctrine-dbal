<?php

namespace jackalope\NodeType;
use jackalope;

/**
 * The NodeTypeDefinition interface provides methods for discovering the
 * static definition of a node type. These are accessible both before and
 * after the node type is registered. Its subclass NodeType adds methods
 * that are relevant only when the node type is "live"; that is, after it
 * has been registered. Note that the separate NodeDefinition interface only
 * plays a significant role in implementations that support node type
 * registration. In those cases it serves as the superclass of both NodeType
 * and NodeTypeTemplate. In implementations that do not support node type
 * registration, only objects implementing the subinterface NodeType will
 * be encountered.
 */
class NodeTypeDefinition implements \PHPCR_NodeType_NodeTypeDefinitionInterface {
    protected $nodeTypeManager;
    
    protected $name;
    protected $isAbstract;
    protected $isMixin;
    protected $isQueryable;
    protected $hasOrderableChildNodes;
    protected $primaryItemName;
    
    protected $declaredSuperTypeNames = array();
    
    protected $declaredPropertyDefinitions = array();
    protected $declaredNodeDefinitions = array();
    
    /**
     * Initializes the NodeTypeDefinition from the given DOM
     * @param DOMElement NodeTypeElement
     */
    public function __construct(DOMElement $node, NodeTypeManager $nodeTypeManager) {
        $this->nodeTypeManager = $nodeTypeManager;
        
        $this->name = $node->getAttribute('name');
        $this->isAbstract = Helper::getBoolAttribute($node, 'isAbstract');
        $this->isMixin = Helper::getBoolAttribute($node, 'isMixin');
        $this->isQueryable = Helper::getBoolAttribute($node, 'isQueryable');
        $this->hasOrderableChildNodes = Helper::getBoolAttribute($node, 'hasOrderableChildNodes');
        
        $this->primaryItemName = $node->getAttribute('primaryItemName');
        if (empty($this->primaryItemName)) {
            $this->primaryItemName = null;
        }
        
        $xp = new DOMXPath($node->ownerDocument);
        $supertypes = $xp->query('supertypes/supertype', $node);
        foreach ($supertypes as $supertype) {
            array_push($this->declaredSuperTypeNames, $supertype->nodeValue);
        }
        
        $properties = $xp->query('propertyDefinition', $node);
        foreach ($properties as $property) {
            array_push($this->declaredPropertyDefinitions, Factory::get(
                'NodeType_PropertyDefinition',
                array($property, $nodeTypeManager)
            ));
        }
        
        $declaredNodeDefinitions = $xp->query('childNodeDefinition', $node);
        foreach ($declaredNodeDefinitions as $nodeDefinition) {
            array_push($this->declaredNodeDefinitions, Factory::get(
                'NodeType_NodeDefinition',
                array($nodeDefinition, $this->nodeTypeManager)
            ));
        }
    }
    
    /**
     * Returns the name of the node type.
     * In implementations that support node type registration, if this
     * NodeTypeDefinition object is actually a newly-created empty
     * NodeTypeTemplate, then this method will return null.
     *
     * @return string a String
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Returns the names of the supertypes actually declared in this node type.
     * In implementations that support node type registration, if this
     * NodeTypeDefinition object is actually a newly-created empty
     * NodeTypeTemplate, then this method will return an array containing a
     * single string indicating the node type nt:base.
     *
     * @return array an array of Strings
     */
    public function getDeclaredSupertypeNames() {
        return $this->declaredSuperTypeNames;
    }

    /**
     * Returns true if this is an abstract node type; returns false otherwise.
     * An abstract node type is one that cannot be assigned as the primary or
     * mixin type of a node but can be used in the definitions of other node
     * types as a superclass.
     *
     * In implementations that support node type registration, if this
     * NodeTypeDefinition object is actually a newly-created empty
     * NodeTypeTemplate, then this method will return false.
     *
     * @return boolean a boolean
     */
    public function isAbstract() {
        return $this->isAbstract;
    }

    /**
     * Returns true if this is a mixin type; returns false if it is primary.
     * In implementations that support node type registration, if this
     * NodeTypeDefinition object is actually a newly-created empty
     * NodeTypeTemplate, then this method will return false.
     *
     * @return boolean a boolean
     */
    public function isMixin() {
        return $this->isMixin;
    }

    /*
     * Returns true if nodes of this type must support orderable child nodes;
     * returns false otherwise. If a node type returns true on a call to this
     * method, then all nodes of that node type must support the method
     * Node.orderBefore. If a node type returns false on a call to this method,
     * then nodes of that node type may support Node.orderBefore. Only the primary
     * node type of a node controls that node's status in this regard. This setting
     * on a mixin node type will not have any effect on the node.
     * In implementations that support node type registration, if this
     * NodeTypeDefinition object is actually a newly-created empty
     * NodeTypeTemplate, then this method will return false.
     *
     * @return boolean a boolean
     */
    public function hasOrderableChildNodes() {
        return $this->hasOrderableChildNodes;
    }

    /**
     * Returns TRUE if the node type is queryable, meaning that the
     * available-query-operators, full-text-searchable and query-orderable
     * attributes of its property definitions take effect. See
     * PropertyDefinition#getAvailableQueryOperators(),
     * PropertyDefinition#isFullTextSearchable() and
     * PropertyDefinition#isQueryOrderable().
     *
     * If a node type is declared non-queryable then these attributes of its
     * property definitions have no effect.
     *
     * @return boolean a boolean
     */
    public function isQueryable() {
        return $this->isQueryable;
    }

    /**
     * Returns the name of the primary item (one of the child items of the nodes
     * of this node type). If this node has no primary item, then this method
     * returns null. This indicator is used by the method Node.getPrimaryItem().
     * In implementations that support node type registration, if this
     * NodeTypeDefinition object is actually a newly-created empty
     * NodeTypeTemplate, then this method will return null.
     *
     * @return string a String
     */
    public function getPrimaryItemName() {
        return $this->primaryItemName;
    }

    /**
     * Returns an array containing the property definitions actually declared
     * in this node type.
     * In implementations that support node type registration, if this
     * NodeTypeDefinition object is actually a newly-created empty
     * NodeTypeTemplate, then this method will return null.
     *
     * @return array an array of PropertyDefinitions
     */
    public function getDeclaredPropertyDefinitions() {
        return $this->declaredPropertyDefinitions;
    }

    /**
     * Returns an array containing the child node definitions actually
     * declared in this node type.
     * In implementations that support node type registration, if this
     * NodeTypeDefinition object is actually a newly-created empty
     * NodeTypeTemplate, then this method will return null.
     *
     * @return array an array of NodeDefinitions
     */
    public function getDeclaredChildNodeDefinitions() {
        return $this->declaredNodeDefinitions;
    }
}
