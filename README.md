# Laravel Chat Api  
  
Service for integration chat functionality in your project.  
  
## Laravel 5.5  
  
  
## Installation and configuration  
Install the ```saritasa/laravel-chat-api``` package:  
  
```bash  
$ composer require saritasa/laravel-chat-api  
```  
  
Publish config with  
```bash  
$ artisan vendor:publish --tag=laravel_chat_api  
```  
  
Update config/laravel_chat_api.php sections:  
- Implement IChatUser contract to your application user model and update parameter `userModelClass`.   
- Check notifications section and add your own notification instead this mocks.   
  
## Work with service  
Add IChatService contract injection in needed class.  
### Methods:  
- Create chat  
```php  
 $chatService->createChat($creator, ['name' => 'New Chat'], [1, 2]);  
 ```
Where [1, 2] - identifiers of participants of chat excluded creator.  

- Close chat  
```php  
 $chatService->closeChat($creator, $chat);
 ``` 
Remember that only creator can close chat and can't close "already closed" chat. In this cases ChatException will be  
thrown.  
- Leave chat  
```php  
 $chatService->leaveChat($user, $chat);
 ```
 When creator leaves chat on of participants became to creator.
- Send message in chat  
```php  
 $chatService->sendMessage($sender, $chat, $message);
 ```
- Mark chat as read  
```php  
 $chatService->markChatAsRead($chat, $user);
 ```
  
## Contributing  
  
1. Create fork  
2. Checkout fork  
3. Develop locally as usual. **Code must follow [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/)**  
4. Update README.md to describe new or changed functionality. Add changes description to CHANGE file.  
5. When ready, create pull request  
  
## Resources  
  
* [Bug Tracker](http://github.com/saritasa/php-laravel-chat-api/issues)  
* [Code](http://github.com/saritasa/php-laravel-chat-api)