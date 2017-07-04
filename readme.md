# route-one - PSR-7 route middleware for advanced middleware routing

<br /><img alt="route-one" title="route-one" src="http://storage8.static.itmages.com/i/17/0702/h_1499027065_4413255_92be76e1d7.png"><br /><br />

**The project is in the beta stage.**

route-one is a [PSR-7](http://www.php-fig.org/psr/psr-7/) compatible middleware aimed to flexibly route request to any other middlewares 
based on request url path, host, http method, etc. It is built on top of [Relay](https://github.com/relayphp/Relay.Relay) and 
[Aura.Router](https://github.com/auraphp/Aura.Router) packages.

route-one is very similar to classic controller routers from every modern framework, but it has some more advantages:
- Standard compliant. You can use any PSR-7 compatible middleware. For example any of these: 
- Allows to built multi-dimensional routes and modify response from group of middlewares.
- It makes your code highly reusable. Any part of the web resource can be bundled as a separate package and used 
in other projects.
 
 



## License

route-one is licensed under a [MIT License](https://opensource.org/licenses/MIT).
