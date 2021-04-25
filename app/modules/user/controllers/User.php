<?php

use App\Core\Controller;

class User extends Controller {

    public function teste($name){
        $dados = array(
            'title' => 'User list',
            'users' => array(
                [
                    'name' => 'Larrire',
                    'emails' => [
                        'linekerlarrire@gmail.com',
                        'liniker_karan@hotmail.com'
                    ]
                ],[
                    'name' => 'Jao das tapioca',
                    'emails' => [
                        'jaodastapioca@gmail.com'
                    ]
                ]
            ),
        );

        $this->template->render('user', $dados);
    }
}