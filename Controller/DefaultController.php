<?php

namespace Controller;

use Exception\BadRequestException;
use Helper\Controller;
use Helper\Form;
use Model\Entities\Cluster;

class DefaultController extends Controller
{
    public function fooAction()
    {
        echo "salut";
    }

    public function chickAction()
    {
        Cluster::findOne(20);
//        return $this->render('home');
//        throw new BadRequestException('Wrong Parameter');
//        $form = new Form();
//        $form->validate();
//        $cluster = Cluster::findOne(2);
//        dump($cluster->users);
    }

    public function signupAction()
    {
        session_start();

        $fb = new \Facebook\Facebook([
            'app_id' => '436390436760517',
            'app_secret' => 'd3caa5716f614971ec3a91a654974e34',
            'default_graph_version' => 'v2.10',
        ]);
        $redirect = 'http://localhost:8888/signup';

        $helper = $fb->getRedirectLoginHelper();

        # Get the access token and catch the exceptions if any
        try {
            $accessToken = $helper->getAccessToken();
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        # If the
        if (isset($accessToken)) {

            // Logged in!
            // Now you can redirect to another page and use the
            // access token from $_SESSION['facebook_access_token']
            // But we shall we the same page
            // Sets the default fallback access token so
            // we don't have to pass it to each request
            $fb->setDefaultAccessToken($accessToken);
            try {
                $response = $fb->get('/me?fields=email,name');
                $userNode = $response->getGraphUser();
            }catch(\Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
            // Print the user Details
            echo "Welcome !<br><br>";
            echo 'Name: ' . $userNode->getName().'<br>';
            echo 'User ID: ' . $userNode->getId().'<br>';
            echo 'Email: ' . $userNode->getProperty('email').'<br><br>';
            $image = 'https://graph.facebook.com/'.$userNode->getId().'/picture?width=200';
            echo "Picture<br>";
            echo "<img src='$image' /><br><br>";

        } else {
            $permissions  = ['email'];
            $loginUrl = $helper->getLoginUrl($redirect,$permissions);
            return $this->render('signup/signup_step_1', ['facebookLogin' => $loginUrl]);
        }
    }
}