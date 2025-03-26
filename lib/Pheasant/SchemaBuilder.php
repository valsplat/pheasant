<?php

namespace Pheasant;

/**
 * Builder for an {@link Schema}.
 */
class SchemaBuilder
{
    private $_pheasant;
    private $_properties = [];
    private $_relationships = [];
    private $_events = [];
    private $_getters = [];
    private $_setters = []
    ;

    public function __construct($pheasant)
    {
        $this->_pheasant = $pheasant;
        $this->_events = new Events([], $pheasant->events());
    }

    /**
     * Sets the schema properties
     * chainable.
     */
    public function properties($map)
    {
        foreach ($map as $name => $type) {
            $this->_properties[$name] = new Property($name, $type);
        }

        return $this;
    }

    /**
     * Sets the schema relationships.
     *
     * @chainable
     */
    public function relationships($map)
    {
        $this->_relationships = $map;

        return $this;
    }

    /**
     * Sets the schema events.
     *
     * @chainable
     */
    public function events($map)
    {
        foreach ($map as $name => $callback) {
            $this->_events->register($name, $callback);
        }

        return $this;
    }

    public function getters($map)
    {
        foreach ($map as $name => $callback) {
            $this->_getters[$name] = $callback;
        }

        return $this;
    }

    public function setters($map)
    {
        foreach ($map as $name => $callback) {
            $this->_setters[$name] = $callback;
        }

        return $this;
    }

    /**
     * Builds a schema object.
     */
    public function build($class)
    {
        if (!isset($this->_properties)) {
            throw new Exception('A schema must have properties');
        }

        return new Schema($class, [
            'properties' => $this->_properties,
            'relationships' => $this->_relationships,
            'getters' => $this->_getters,
            'setters' => $this->_setters,
            'events' => $this->_events,
        ]);
    }
}
