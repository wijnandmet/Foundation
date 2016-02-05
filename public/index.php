<?php 
header('Content-Type: text/html; charset=utf-8');

//require_once '../autoload.php';
require_once __DIR__.'/../vendor/autoload.php';


/*
working example 1
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing;

$request = Request::createFromGlobals();
$routes = include __DIR__.'/../resources/app.php';

$context = new Routing\RequestContext();
$context->fromRequest($request);
$matcher = new Routing\Matcher\UrlMatcher($routes, $context);

try {
    extract($matcher->match($request->getPathInfo()), EXTR_SKIP);
    ob_start();
    //include sprintf(__DIR__.'/../src/pages/%s.php', $_route);
    echo 'hoi';

    $response = new Response(ob_get_clean());
} catch (Routing\Exception\ResourceNotFoundException $e) {
    $response = new Response('Not Found', 404);
} catch (Exception $e) {
    $response = new Response('An error occurred', 500);
}

$response->send();



$generator = new Routing\Generator\UrlGenerator($routes, $context);
echo $generator->generate('hello', array('name' => 'Fabien'),true);// true = fullurl , false = /public/...
*/

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing;
use Symfony\Component\HttpKernel;

$request = Request::createFromGlobals();
$routes = include __DIR__.'/../foundation/resources/app.php';

$context = new Routing\RequestContext();
$context->fromRequest($request);
$matcher = new Routing\Matcher\UrlMatcher($routes, $context);
$resolver = new HttpKernel\Controller\ControllerResolver();

$framework = new Foundation\Libraries\Base\Framework($matcher, $resolver);
$response = $framework->handle($request);

$response->send();


/*
function render_template($request)
{
    extract($request->attributes->all(), EXTR_SKIP);
    ob_start();
    include sprintf(__DIR__.'/../src/pages/%s.php', $_route);

    return new Response(ob_get_clean());
}

try {
    $request->attributes->add($matcher->match($request->getPathInfo()));
    $response = call_user_func($request->attributes->get('_controller'), $request);
} catch (Routing\Exception\ResourceNotFoundException $e) {
    $response = new Response('Not Found', 404);
} catch (Exception $e) {
    $response = new Response('An error occurred', 500);
}

// render de array als attributes, en dat wordt de nieuwe content :-)
$routes->add('hello', new Routing\Route('/hello/{name}', array(
    'name' => 'World',
    '_controller' => function ($request) {
        // $foo will be available in the template
        $request->attributes->set('foo', 'bar');

        $response = render_template($request);

        // change some header
        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }
)));




// controllers



// the controller itself
class LeapYearController
{
    public function indexAction($year, Request $request) // typehinting works
    {
        if (is_leap_year($year)) {
            return new Response('Yep, this is a leap year!');
        }

        return new Response('Nope, this is not a leap year.');
    }
}


*/

exit;


// hier een header class voor maken

$request = Request::createFromGlobals();

$input = $request->get('name', 'World');

$response = new Response(sprintf('Hello %s', htmlspecialchars($input, ENT_QUOTES, 'UTF-8')));

$response->send();

echo '<pre>';
// the URI being requested (e.g. /about) minus any query parameters
var_dump($request->getPathInfo());

// retrieve GET and POST variables respectively
var_dump($request->query->get('foo'));
var_dump($request->request->get('bar', 'default value if bar does not exist'));

// retrieve SERVER variables
var_dump($request->server->get('HTTP_HOST'));

// retrieves an instance of UploadedFile identified by foo
var_dump($request->files->get('foo'));

// retrieve a COOKIE value
var_dump($request->cookies->get('PHPSESSID'));

// retrieve an HTTP request header, with normalized, lowercase keys
var_dump($request->headers->get('host'));
var_dump($request->headers->get('content_type'));

var_dump($request->getMethod());    // GET, POST, PUT, DELETE, HEAD
var_dump($request->getLanguages()); // an array of languages the client accepts

//$request = Request::create('/index.php?name=Fabien');
echo '</pre>';

$response = new Response();

$response->setContent('Hello world!');
$response->setStatusCode(200);
$response->headers->set('Content-Type', 'text/css');

// configure the HTTP cache headers
$response->setMaxAge(10);


if ($myIp == $request->getClientIp()) { // save ipcheck
    // the client is a known one, so give it some more privilege
}


// 'routes'
$map = array(
    '/hello' => __DIR__.'/hello.php',
    '/bye'   => __DIR__.'/bye.php',
);
$path = $request->getPathInfo();
if (isset($map[$path])) {
    require $map[$path];
} else {
    $response->setStatusCode(404);
    $response->setContent('Not Found');
}
$response->send();

// test a url
$request = Request::create('/hello?name=Fabien');
?>