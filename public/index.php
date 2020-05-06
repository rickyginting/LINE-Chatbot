<?php
require __DIR__ . '/../vendor/autoload.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
$pass_signature       = true;
$channel_access_token = "";
$channel_secret       = "";
$httpClient           = new CurlHTTPClient($channel_access_token);
$bot                  = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
$app                  = AppFactory::create();
$app->setBasePath("/public");
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Bot Remmar Martin Created by : Ricky Martin Ginting");
    return $response;
});
$app->post('/webhook', function (Request $request, Response $response) use ($channel_secret, $bot, $httpClient, $pass_signature) {
    $body      = $request->getBody();
    $signature = $request->getHeaderLine('HTTP_X_LINE_SIGNATURE');
    file_put_contents('php://stderr', 'Body: ' . $body);
    if ($pass_signature === false) {
        if (empty($signature)) {
            return $response->withStatus(400, 'Signature not set');
        }
        if (!SignatureValidator::validateSignature($body, $channel_secret, $signature)) {
            return $response->withStatus(400, 'Invalid signature');
        }
    }
    $data = json_decode($body, true);
    if (is_array($data['events'])) {
        foreach ($data['events'] as $event) {
            if ($event['type'] == 'message') {
                if ($event['message']['type'] == 'text') {
                    if (strtolower($event['message']['text']) == 'user id') {
                        $result = $bot->replyText($event['replyToken'], $event['source']['userId']);
                    } elseif (strtolower($event['message']['text']) == 'youtube') {
                        $flexTemplate = file_get_contents("../flex_youtube.json"); // template flex message
                        $result       = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                            'replyToken' => $event['replyToken'],
                            'messages'   => [
                                [
                                    'type'     => 'flex',
                                    'altText'  => 'Youtube Flex Message',
                                    'contents' => json_decode($flexTemplate),
                                ],
                            ],
                        ]);
                    } elseif (strtolower($event['message']['text']) == 'facebook') {
                        $flexTemplate = file_get_contents("../flex_facebook.json"); // template flex message
                        $result       = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                            'replyToken' => $event['replyToken'],
                            'messages'   => [
                                [
                                    'type'     => 'flex',
                                    'altText'  => 'Facebook Flex Message',
                                    'contents' => json_decode($flexTemplate),
                                ],
                            ],
                        ]);
                    } elseif (strtolower($event['message']['text']) == 'foto') {
                        $flexTemplate = file_get_contents("../flex_foto.json"); // template flex message
                        $result       = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                            'replyToken' => $event['replyToken'],
                            'messages'   => [
                                [
                                    'type'     => 'flex',
                                    'altText'  => 'Foto Flex Message',
                                    'contents' => json_decode($flexTemplate),
                                ],
                            ],
                        ]);
                    } elseif (strtolower($event['message']['text']) == 'format') {
                        $flexTemplate = file_get_contents("../flex_format.json"); // template flex message
                        $result       = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                            'replyToken' => $event['replyToken'],
                            'messages'   => [
                                [
                                    'type'     => 'flex',
                                    'altText'  => 'Format Flex Message',
                                    'contents' => json_decode($flexTemplate),
                                ],
                            ],
                        ]);
                    } elseif (strtolower($event['message']['text']) == 'how are you') {
                        $result = $bot->replyText($event['replyToken'], "I'm fine but I miss you too, I'll wait for you where we can meet when Covid-19 is over");
                    } elseif (strtolower($event['message']['text']) == 'sayang') {
                        $result = $bot->replyText($event['replyToken'], "Ya sayang");
                    } elseif (strtolower($event['message']['text']) == 'sudah makan') {
                        $result = $bot->replyText($event['replyToken'], "Sudah dong, bagaiman dengan kamu");
                    } elseif (strtolower($event['message']['text']) == 'sudah juga') {
                        $result = $bot->replyText($event['replyToken'], "Baguslah kalo begitu, ada yang bisa Remmar bantu ?");
                    } elseif (strtolower($event['message']['text']) == 'tidak') {
                        $result = $bot->replyText($event['replyToken'], "Baiklah kalo begitu");
                    } elseif (strtolower($event['message']['text']) == 'covid') {
                        $api       = file_get_contents("https://api.kawalcorona.com/indonesia/");
                        $json      = json_decode($api, TRUE);
                        $positif   = $json[0]['positif'];
                        $sembuh    = $json[0]['sembuh'];
                        $meninggal = $json[0]['meninggal'];
                        $result    = $bot->replyText($event['replyToken'], "Remmar akan bagikan informasi Covid di Indonesia \nPositif : " . $positif . "\nSembuh : " . $sembuh . "\nMeninggal : " . $meninggal . "\nKamu tetap dirumah dan jaga kesehatan ya sayang");
                    } else {
                        $result = $bot->replyText($event['replyToken'], "Remmar belum begitu memahami bahasa kamu ! \nketikan format untuk melihat format yang ada");
                    }

                    $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());
                } elseif (
                    $event['message']['type'] == 'image' or
                    $event['message']['type'] == 'video' or
                    $event['message']['type'] == 'audio' or
                    $event['message']['type'] == 'file'
                ) {
                    $contentURL  = " https://bottesz.herokuapp.com/public/content/" . $event['message']['id'];
                    $contentType = ucfirst($event['message']['type']);
                    $result      = $bot->replyText($event['replyToken'],
                        $contentType . " File kamu bisa di akses melalui link berikut ini :\n " . $contentURL);
                    $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());
                } //group room
                elseif (
                    $event['source']['type'] == 'group' or
                    $event['source']['type'] == 'room'
                ) {
                    if ($event['source']['userId']) {
                        $userId     = $event['source']['userId'];
                        $getprofile = $bot->getProfile($userId);
                        $profile    = $getprofile->getJSONDecodedBody();
                        $greetings  = new TextMessageBuilder("Halo, " . $profile['displayName']);
                        $result     = $bot->replyMessage($event['replyToken'], $greetings);
                        $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                        return $response
                            ->withHeader('Content-Type', 'application/json')
                            ->withStatus($result->getHTTPStatus());
                    }
                } else {
                    $result = $bot->replyText($event['replyToken'], $event['message']['text']);
                    $response->getBody()->write((string) $result->getJSONDecodedBody());
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());
                }
            }
        }
    }
    return $response->withStatus(400, 'No event sent!');
});

$app->get('/content/{messageId}', function ($req, $response, $args) use ($bot) {
// get message content
    $messageId = $args['messageId'];
    $result    = $bot->getMessageContent($messageId);
// set response
    $response->getBody()->write($result->getRawBody());
    return $response
        ->withHeader('Content-Type', $result->getHeader('Content-Type'))
        ->withStatus($result->getHTTPStatus());
});

$app->get('/pushmessage', function ($req, $response) use ($bot) {
// send push message to user
    $userId             = 'U997d5d7dacf751c75053e51d59402996';
    $textMessageBuilder = new TextMessageBuilder('Hello honey I miss you');
    $result             = $bot->pushMessage($userId, $textMessageBuilder);
    $response->getBody()->write("Pesan push berhasil dikirim!");
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($result->getHTTPStatus());
});

$app->get('/profile/{userId}', function ($req, $response, $args) use ($bot) {
// get user profile
    $userId = $args['userId'];
    $result = $bot->getProfile($userId);
    $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($result->getHTTPStatus());
});

$app->run();