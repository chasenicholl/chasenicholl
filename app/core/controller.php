<?php
class Controller
{
    
    public $name   = "";
    public $view   = "";
    public $layout = array();
    
    public $request = array();
    protected $_request = array(
        'body' => null,
        'get'  => null,
        'post' => null
    );
    public $data = array();
    
    public function __construct($request = array())
    {
        $request_body = $this->getRequestBody();
        if (!empty($request_body)) {
            $this->setRequest($request_body, 'body');       
        }
        $this->setRequest($_GET, 'get');
        $this->setRequest($_POST, 'post');
    }
    
    public function __call($name, $values)
    {
    
        switch (strtolower(substr($name, 0, 3))) {
            
            case 'get':
                
                $name = preg_replace('/get/', '', $name);
                $key = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
                
                if (isset($this->request[$key])) {
                    return $this->request[$key];
                }
                
                break;
        }
        
    }
    
    public function getRequestBody()
    {
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            return json_decode(file_get_contents('php://input'), true);
        }
        return null;
    }
    
    public function setRequest($request = array(), $type)
    {
        $this->_request[$type] = $request;
        return $this;
    }
    
    public function getRequest($type = null)
    {
        if (!empty($type)) {
            $this->request = $this->_request[$type];
        }
        else {
            $this->request = $this->_request;    
        }
        return $this->request;
    }
    
    public function load($name = "")
    {
        $this->controller_name = $name . "_controller";
        $controller            = Comet::camalizeClassName($this->controller_name);
        
        if (Comet::isAppFileByClass($controller)) {
            return new $controller();
        }
            
        return null;
    }
    
    public function renderViewByName($name = "", $type = "backend")
    {
        $this->view_type = $type;
        $this->view_name = $name;
        
        $view_path = get_include_path() . "/views/" . $type . "/" . $name . ".php";
        if (file_exists($view_path)) {
            $this->renderView($view_path, $type);
        }
    }
    
    public function renderView($view_path = "", $type = "")
    {
        # Extract variables for the view
        if (!empty($this->data)) {
            foreach ($this->data as $data) {
                extract($data);
            }
        }

        $this->setLayout();
        include_once(get_include_path() . "/includes/header.php");
        include_once($view_path);
        include_once(get_include_path() . "/includes/footer.php");
    }
    
    public function setData($key, $value)
    {
        $this->data[] = array($key => $value);
    }
    
    public function setLayout()
    {
        
        $global_layout_file = get_include_path() . 
            "/layouts/global.json";
            
        $global_layout = $this->_getLayoutFile($global_layout_file);        
        
        $page_layout_file = get_include_path() . 
            "/layouts/" . $this->name . "/" . $this->view . ".json";
        
        $page_layout = $this->_getLayoutFile($page_layout_file);
        
        $this->layout = $this->_mergeLayouts($global_layout, $page_layout);
                
    }
    
    public function renderHead()
    {
        
        $header = "";
        
        if (!empty($this->layout)) {
            
            # Set title
            if (isset($this->layout['title'])) {
                $header .= "<title>{$this->layout['title']}</title>\n";   
            }
            
            # Set Meta
            if (isset($this->layout['meta'])) {
                
                foreach ($this->layout['meta'] as $meta) {
                    
                    $header .= "<meta ";
                    foreach ($meta as $key => $val) {
                        $header .= $key . '="'.$val.'" ';
                    }
                    $header .= "/>\n";
                                        
                }
                
            }
            
            # Set Assets
            if (isset($this->layout['assets'])) {
            
                # Load CSS
                if (isset($this->layout['assets']['css'])) {
                    
                    $css_dir = Comet::getDir('css');
                    
                    foreach ($this->layout['assets']['css'] as $css) {
                        
                        if ($css['placement'] == 'header') {
                            $header .= "<link rel=\"stylesheet\" type=\"text/css\" ";
                            $header .= "href=\"{$css_dir}{$css['source']}\" />\n";
                        }
                        
                    }
                    
                }
                
                # Load JS
                if (isset($this->layout['assets']['js'])) {
                    
                    $js_dir = Comet::getDir('js');
                    
                    $script_count = count($this->layout['assets']['js']);
                    $header .= "<script type=\"text/javascript\">\n";
                    $header .= "var COMET_SCRIPT_COUNT = {$script_count};\n";
                    $header .= "</script>\n";                    
                    
                    foreach ($this->layout['assets']['js'] as $js) {
                        
                        if ($js['placement'] == 'header') {
                            $header .= "<script type=\"text/javascript\" ";
                            $header .= "src=\"{$js_dir}{$js['source']}\" onload=\"Comet.loaded();\"></script>\n";
                        }
                        
                    }
                    
                }
            
            }
            
        }
            
        echo $header;
        
    }
    
    public function renderFooter()
    {
        
        $footer = "\n";
        
        # Set Assets
        if (isset($this->layout['assets'])) {
            
            # Load JS
            if (isset($this->layout['assets']['js'])) {
                
                $js_dir = Comet::getDir('js');
                
                foreach ($this->layout['assets']['js'] as $js) {

                    if ($js['placement'] == 'footer') {
                        $footer .= "<script type=\"text/javascript\" ";
                        $footer .= "src=\"{$js_dir}{$js['source']}\" onload=\"Comet.loaded();\"></script>\n";
                    }
                    
                }
                
            }
        
        }
        
        echo $footer;
        
    }
    
    protected function _getLayoutFile($layout_file)
    {
        if (is_file($layout_file)) {
            
            $json   = file_get_contents($layout_file);
            $layout = json_decode($json, true);
            
            if (!empty($layout)) {
                return $layout;    
            }
        
        }
        
        return false;
    }
    
    protected function _mergeLayouts($global, $page)
    {
        
        $layout = array();
        $layout = $global;
        
        # Page overrides and merges
        if (isset($page['title']) && !empty($page['title'])) {
            $layout['title'] = $page['title'];
        }
        
        # Merge Meta Data
        if (isset($global['meta']) && isset($page['meta'])) {
                        
            if (!isset($layout['meta'])) {
                $layout['meta'] = array();
            }
            
            $layout['meta'] = array_unique(array_merge($layout['meta'], $page['meta']), SORT_REGULAR);
                                    
            $_meta = array();
            foreach ($layout['meta'] as $key => $meta) {
                if (isset($meta['name'])) {
                    $_meta[$meta['name']] = $meta;
                }
                else {
                    $_meta[] = $meta;
                }
            }
            
            sort($_meta);
            $layout['meta'] = $_meta;

        }
        
        # Merge assets
        if (isset($page['assets'])) {
            
            if (isset($page['assets']['css'])) {
                
                if (!isset($layout['assets'])) {
                    $layout['assets'] = array();
                }
                
                if (!isset($layout['assets']['css'])) {
                    $layout['assets']['css'] = array();
                }
                
                $layout['assets']['css'] = array_merge($layout['assets']['css'], $page['assets']['css']);
                
            }
            
            if (isset($page['assets']['js'])) {
                
                if (!isset($layout['assets'])) {
                    $layout['assets'] = array();
                }
                
                if (!isset($layout['assets']['js'])) {
                    $layout['assets']['js'] = array();
                }
                
                $layout['assets']['js'] = array_merge($layout['assets']['js'], $page['assets']['js']);
                
            }
            
        }
                
        return $layout;
        
    }
        
    public function setUserPayload()
    {        
        
        $user = Users::getUser();
        
        if ($user && $user->isHydrated()) {
                            
            $_avatar_flag = $user->getAvatar();
            $_avatar = (empty($_avatar_flag) ? false : $_avatar_flag);
            
            if ($_avatar) {
                $id     = $user->getId();
                $avatar = "/media/Users/admin{$id}/avatar.jpg";
            }
            
            $payload = array(
                'id'       => $user->getId(),
                'email'    => $user->getEmail(),
                'username' => $user->getUsername(),
                'avatar'   => $avatar
            );
            
            $this->setData('user_payload', $payload);
            return;
        }
        
        $this->setData('user_payload', false);
        
    }
    
    public function getTemplate($template = "")
    {
        # Extract variables for the template
        if (!empty($this->data)) {
            foreach ($this->data as $data) {
                extract($data);
            }
        }
        
        $file = get_include_path() . "/templates/{$template}.php";
        if (is_file($file)) {
            echo "\n";
            include_once($file);
        }
    }
    
    public function checkUserRole()
    {
        $user = Users::getUser();
        if ($user && $user->isHydrated()) {
            
            $group_id    = $user->getUserGroupId();
            $group       = Model::get('user_groups')->load($group_id);
            $group_roles = $group->getRoles();
            $roles       = array();
            
            foreach ($group_roles as $role_id) {
                $roles[] = Model::get('user_roles')->load($role_id);
            }
            
            if (!empty($roles)) {
                # Do something with roles?                
            }
            
        }
    }
    
    public function redirectTo($url = "")
    {
        header("Location: {$url}");
        exit();
    }
    
    public function redirectBack()
    {
        header("Location: {$_SERVER['HTTP_REFERER']}");
        exit();
    }
    
    public function getGridSorting()
    {
        if (!isset($_GET['page'])) {
            $page = 1;
        }
        elseif (is_numeric($_GET['page'])) {
            $page = $_GET['page'];
        }
        else {
            $page = 1;
        }
        
        if (!isset($_GET['sort'])) {
            $sort = 'updated_at';
        }
        else {
            $sort = $_GET['sort'];
        }
        
        if (!isset($_GET['direction'])) {
            $direction = 'desc';
        }
        else {
            $direction = $_GET['direction'];
        }
        
        if ($direction != 'desc') {
            $direction = 'asc';
            $next_direction = 'desc';
        }
        
        if ($direction != 'asc') {
            $direction = 'desc';
            $next_direction = 'asc';
        }
        
        return array(
            'page'           => $page,
            'sort'           => $sort,
            'direction'      => $direction,
            'next_direction' => $next_direction
        );
    } 
    
}