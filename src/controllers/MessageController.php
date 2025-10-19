<?php

class MessageController extends Controller
{
    public function index()
    {
        $pageTitle = 'Messagerie - Tom Troc';

        // Conversations fictives
        $conversations = [
            [
                'id' => 1,
                'username' => 'Alexlecture',
                'avatar' => '/tomtroc/public/images/avatar-1.jpg',
                'lastMessage' => 'Lorem ipsum dolor sit amet, consectetur .adipiscing elit, sed do eiusmod tempor',
                'time' => '15:43',
                'unread' => false
            ],
            [
                'id' => 2,
                'username' => 'Nathalire',
                'avatar' => '/tomtroc/public/images/avatar-2.jpg',
                'lastMessage' => 'Lorem ipsum dolor sit amet, consectetur .adipiscing elit, sed do eiusmod tempor',
                'time' => '20.08',
                'unread' => false
            ],
            [
                'id' => 3,
                'username' => 'Sas634',
                'avatar' => '/tomtroc/public/images/avatar-3.jpg',
                'lastMessage' => 'Lorem ipsum dolor sit amet, consectetur .adipiscing elit, sed do eiusmod tempor',
                'time' => '15.08',
                'unread' => false
            ]
        ];

        // Messages de la conversation active
        $messages = [
            [
                'id' => 1,
                'sender' => 'other',
                'text' => 'Lorem ipsum dolor sit amet, consectetur .adipiscing elit, sed do eiusmod tempor',
                'time' => '21.08',
                'date' => '15:44'
            ],
            [
                'id' => 2,
                'sender' => 'me',
                'text' => 'Lorem ipsum dolor sit amet, consectetur .adipiscing elit, sed do eiusmod tempor',
                'time' => '21.08',
                'date' => '15:48'
            ]
        ];

        $this->render('messages/index', [
            'pageTitle' => $pageTitle,
            'conversations' => $conversations,
            'messages' => $messages,
            'activeUser' => 'Alexlecture',
            'activeAvatar' => '/tomtroc/public/images/avatar-1.jpg'
        ]);
    }

    public function send()
    {
        // Traitement de l'envoi de message
        // Sera implémenté plus tard avec la base de données
        header('Location: /tomtroc/messages');
        exit;
    }
}
