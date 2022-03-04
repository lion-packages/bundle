<?php

namespace LionRoute;

class Middleware implements \JsonSerializable {

	public function __construct(
		private ?string $middlewareName = null, 
		private ?string $objectClass = null, 
		private ?string $methodClass = null
	) {}

	public function jsonSerialize() {
        return get_object_vars($this);
    }

    public function getNewObjectClass() {
        $objectClass = $this->getObjectClass();
        return new $objectClass();
    }

    public function getMiddlewareName() {
        return $this->middlewareName;
    }

    public function setMiddlewareName($middlewareName) {
        $this->middlewareName = $middlewareName;
        return $this;
    }

    public function getObjectClass() {
        return $this->objectClass;
    }

    public function setObjectClass($objectClass) {
        $this->objectClass = $objectClass;
        return $this;
    }

    public function getMethodClass() {
        return $this->methodClass;
    }

    public function setMethodClass($methodClass) {
        $this->methodClass = $methodClass;
        return $this;
    }

}