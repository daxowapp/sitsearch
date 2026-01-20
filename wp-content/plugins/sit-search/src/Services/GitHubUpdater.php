<?php

namespace SIT\Search\Services;

class GitHubUpdater {

    private $slug; // plugin slug
    private $pluginData; // plugin data
    private $username; // GitHub username
    private $repo; // GitHub repo name
    private $pluginFile; // __FILE__ of our plugin
    private $githubAPIResult; // holder for 
    private $accessToken; // private repo token

    public function __construct($pluginFile, $githubUsername, $githubRepo, $accessToken = '') {
        $this->pluginFile = $pluginFile;
        $this->username = $githubUsername;
        $this->repo = $githubRepo;
        $this->accessToken = $accessToken;
    }

    public function init() {
        $this->slug = plugin_basename($this->pluginFile);
        add_filter('pre_set_site_transient_update_plugins', array($this, 'setTransients'));
        add_filter('plugins_api', array($this, 'setPluginInfo'), 10, 3);
        add_filter('upgrader_post_install', array($this, 'postInstall'), 10, 3);
    }

    private function getRepoInfo() {
        if (!empty($this->githubAPIResult)) {
            return $this->githubAPIResult;
        }

        // Query the GitHub API
        $url = "https://api.github.com/repos/{$this->username}/{$this->repo}/releases/latest";
        
        // We need to set a User-Agent for GitHub API
        $args = array(
            'headers' => array(
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url')
            )
        );

        if (!empty($this->accessToken)) {
            $args['headers']['Authorization'] = "Bearer {$this->accessToken}";
        }

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            return false;
        }

        $this->githubAPIResult = json_decode(wp_remote_retrieve_body($response));
        return $this->githubAPIResult;
    }

    public function setTransients($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $this->pluginData = get_plugin_data($this->pluginFile);
        $remote = $this->getRepoInfo();

        if ($remote && isset($remote->tag_name)) {
            // Compare versions
            $remote_version = str_replace('v', '', $remote->tag_name);
            
            if (version_compare($this->pluginData['Version'], $remote_version, '<')) {
                $obj = new \stdClass();
                $obj->slug = $this->slug;
                $obj->new_version = $remote_version;
                $obj->url = $remote->html_url;
                $obj->package = $remote->zipball_url;
                
                // Add authentication for private repos during download
                if (!empty($this->accessToken)) {
                     $obj->package = add_query_arg(array('access_token' => $this->accessToken), $obj->package);
                }

                $transient->response[$this->slug] = $obj;
            }
        }
        
        return $transient;
    }

    public function setPluginInfo($false, $action, $response) {
        if (empty($response->slug) || $response->slug != $this->slug) {
            return false;
        }

        $remote = $this->getRepoInfo();

        if ($remote && isset($remote->tag_name)) {
            $obj = new \stdClass();
            $obj->slug = $this->slug;
            $obj->name = $this->pluginData['Name'];
            $obj->plugin_name = $this->slug;
            $obj->new_version = str_replace('v', '', $remote->tag_name);
            $obj->requires = '6.0';
            $obj->tested = '6.7';
            $obj->download_link = $remote->zipball_url;
            $obj->trunk = $remote->zipball_url;
            $obj->sections = array(
                'description' => $remote->body
            );

            return $obj;
        }

        return false;
    }

    public function postInstall($true, $hook_extra, $result) {
        // Post-install logic if needed (e.g. rename folder back to original slug if GitHub adds -master)
        return $result;
    }
}
