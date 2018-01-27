Component-Based Software Engineering
====================================

Recursive and Dynamic Software Composition with Sharing
-------------------------------------------------------
http://fractal.ow2.org/current/fractalWCOP02.pdf
* Encapsulation and Identity
* Composition
* Sharing (reusability)
* Life-cycle
* Activities
* Control
* Mobility

The Fractal Open Component Model
--------------------------------
http://fractal.ow2.org/doc/ow2-webinars09/Fractal-JBS.pdf
* Components: units of (data and behavior) encapsulation with well
defined interfaces for communication with other components
### Fractal: classical concepts
#### Component
* Encapsulated data and behavior 
* With well identified interfaces
* With sub-components
#### Interface
* A named access point to a component
* Can emit or receive operations or messages
E.g. client and server interfaces
* Can be typed
#### Binding
* Communication path between components
* Bindings mediate all interactions between components

### Fractal:  original concepts
#### Component = membrane + sub-components
##### Membrane 
* Supports a component’s reflective capabilities
* Can support meta-object protocols through control interfaces
* Can have an internal structure of its own (cf AOKell)
* Arbitrary reflective capabilities can be supported (no fixed MOP)
* Sharing: A component can be a subcomponent of several composites.
Component graphs, not just trees
* Useful for architectures with resources and cross-cutting concerns

##### Bindings
* Can support arbitrary communication semantics request/response, 
asynchronous message dispatch, publish/subscribe,etc.
* Can be primitive or composite
* A primitive binding connects two or more interfaces in the same
address space; typically implemented by a language reference.
* A composite binding connects two or more interfaces; can be reified as a 
component with primitive bindings.
* Can span address spaces and networks
###### Reflection: minimal
* Component controller (discovering a component interfaces)
* Interface controller (obtaining the Component controller)
* Binding controller (binding an external component interface)
###### Reflection: structural
* Content controller (adding, removing subcomponents)
* Attribute controller (setting, getting component attributes)
###### Reflection: behavioral
* Interceptors (before, around and after operations)
* Lifecycle controller (starting, stopping the component)