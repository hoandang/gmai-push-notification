<?php
$response = json_decode(file_get_contents("php://input"))->message->data;
if ($response)
{
  require_once '../vendor/autoload.php';
  require_once './helpers.php';

  $client = getClient();
  $service = new Google_Service_Gmail($client);

  $messages = $service->users_messages->listUsersMessages('me', [
    'includeSpamTrash' => false,
    'labelIds' => ['INBOX', 'UNREAD'],
    'q' => 'from:calendar-notification@google.com'
  ]);

  foreach($messages as $messageData)
  {
    $message = $service->users_messages->get('me', $messageData->id, ['format' => 'full']);
    $payload = $message->getPayload();
    $headers = $payload->getHeaders();
    $subject = trim(getHeader($headers, 'Subject'));

    file_put_contents('foo', $subject . "\n", FILE_APPEND);

    $modifyMessageRequest = new Google_Service_Gmail_ModifyMessageRequest;
    $modifyMessageRequest->setRemoveLabelIds(['UNREAD']);
    $service->users_messages->modify('me', $messageData->id, $modifyMessageRequest);
  }
}
