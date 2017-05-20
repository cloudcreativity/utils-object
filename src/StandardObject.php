<?php

/**
 * Copyright 2017 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace CloudCreativity\Utils\Object;

use ArrayIterator;
use IteratorAggregate;
use OutOfBoundsException;
use stdClass;
use Traversable;

/**
 * Class StandardObject
 *
 * @package CloudCreativity\Utils\Object
 */
class StandardObject implements IteratorAggregate, StandardObjectInterface
{

    use ObjectProxyTrait;

    /**
     * @param object|null $proxy
     */
    public function __construct($proxy = null)
    {
        $this->proxy = $proxy ?: new stdClass();
    }

    /**
     * @return void
     */
    public function __clone()
    {
        $this->proxy = Obj::replicate($this->proxy);
    }

    /**
     * @return StandardObject
     */
    public function copy()
    {
        return clone $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        if (!$this->has($key)) {
            throw new OutOfBoundsException(sprintf('Key "%s" does not exist.', $key));
        }

        return $this->proxy->{$key};
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->has($key);
    }

    /**
     * @param $key
     */
    public function __unset($key)
    {
        $this->remove($key);
    }

    /**
     * @return Traversable
     */
    public function getIterator()
    {
        if ($this->proxy instanceof Traversable) {
            return clone $this->proxy;
        }

        return new ArrayIterator((array) $this->proxy);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->toArray());
    }
}
