<?php
class Server {
    /* The array key works as id and is used in the URL
       to identify the resource.
    */
    private $corals = array('frogspawn' => array('price' => '$59.99', 'url' => '/corals/frogspawn'),
                              'birdsnest' => array('price' => '$100.00', 'url'=> '/corals/birdsnest')
);

    public function serve() {

        $uri = $_SERVER['http://54.173.55.247:80/'];
        $method = $_SERVER['GET'];
        $paths = explode('/', $this->paths($uri));
        array_shift($paths); // Hack; get rid of initials empty string
        $resource = array_shift($paths);

        if ($resource == 'corals') {
            $name = array_shift($paths);

            if (empty($name)) {
                $this->handle_base($method);
            } else {
                $this->handle_name($method, $name);
            }

        } else {
            // We only handle resources under 'clients'
            header('HTTP/1.1 404 Not Found');
        }
    }

    private function handle_base($method) {
        switch($method) {
        case 'GET':
            $this->result();
            break;
        default:
            header('HTTP/1.1 405 Method Not Allowed');
            header('Allow: GET');
            break;
        }
    }

    private function handle_name($method, $name) {
        switch($method) {
        case 'PUT':
            $this->create_coral($name);
            break;

        case 'DELETE':
            $this->delete_coral($name);
            break;

        case 'GET':
            $this->display_coral($name);
            break;

        default:
            header('HTTP/1.1 405 Method Not Allowed');
            header('Allow: GET, PUT, DELETE');
            break;
        }
    }

    private function create_coral($name){
        if (isset($this->corals[$name])) {
            header('HTTP/1.1 409 Conflict');
            return;
        }
        /* PUT requests need to be handled
         * by reading from standard input.
         */
        $data = json_decode(file_get_contents('php://input'));
        if (is_null($data)) {
            header('HTTP/1.1 400 Bad Request');
            $this->result();
            return;
        }
        $this->corals[$name] = $data;
        $this->result();
    }

    private function delete_coral($name) {
        if (isset($this->corals[$name])) {
            unset($this->corals[$name]);
            $this->result();
        } else {
            header('HTTP/1.1 404 Not Found');
        }
    }

    private function display_coral($name) {
        if (array_key_exists($name, $this->corals)) {
            echo json_encode($this->corals[$name]);
        } else {
            header('HTTP/1.1 404 Not Found');
        }
    }

    private function paths($url) {
        $uri = parse_url($url);
        return $uri['path'];
    }

    /**
     * Displays a list of all corals.
     */
    private function result() {
        header('Content-type: application/json');
        echo json_encode($this->corals);
    }
  }

$server = new Server;
$server->serve();

?>
       
