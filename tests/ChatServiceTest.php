<?php

namespace Saritasa\LaravelChatApi\Tests;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Str;
use League\Flysystem\Config;
use PHPUnit\Framework\TestCase;
use Mockery;
use Mockery\MockInterface;
use Saritasa\LaravelChatApi\Contracts\IChat;
use Saritasa\LaravelChatApi\Contracts\IChatParticipant;
use Saritasa\LaravelChatApi\Exceptions\ChatException;
use Saritasa\LaravelChatApi\Models\Chat;
use Saritasa\LaravelChatApi\Models\ChatParticipant;
use Saritasa\LaravelEntityServices\Contracts\IEntityServiceFactory;
use Saritasa\LaravelEntityServices\Contracts\IEntityService;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Saritasa\LaravelChatApi\Contracts\INotificationsFactory;
use Saritasa\LaravelChatApi\Services\ChatService;
use Saritasa\LaravelChatApi\Contracts\IChatUser;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceException;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Chat service unit tests.
 *
 * @see ChatService
 */
class ChatServiceTest extends TestCase
{
    /**
     * Service entity factory mock.
     *
     * @var MockInterface|IEntityServiceFactory
     */
    protected $serviceEntityFactoryMock;

    /**
     * Chat service mock.
     *
     * @var MockInterface|IEntityService
     */
    protected $chatServiceEntityMock;

    /**
     * Chat participant mock.
     *
     * @var MockInterface|IEntityService
     */
    protected $chatParticipantServiceEntityMock;

    /**
     * Notification dispatcher mock.
     *
     * @var MockInterface|Dispatcher
     */
    protected $dispatcherMock;

    /**
     * Database connection mock.
     *
     * @var MockInterface|ConnectionInterface
     */
    protected $connectionMock;

    /**
     * Notification factory mock.
     *
     * @var MockInterface|INotificationsFactory
     */
    protected $notificationFactory;

    /**
     * Chat user mock.
     *
     * @var MockInterface|IChatUser
     */
    protected $chatUserMock;

    /**
     * Event dispatcher mock.
     *
     * @var MockInterface|IChatUser
     */
    protected $eventDispatcher;

    /**
     * Prepare configuration and mocks needed to testing.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->chatServiceEntityMock = Mockery::mock(IEntityService::class);
        $this->chatParticipantServiceEntityMock = Mockery::mock(IEntityService::class);
        $this->serviceEntityFactoryMock = Mockery::mock(IEntityServiceFactory::class);
        $this->serviceEntityFactoryMock->shouldReceive('build')
            ->withArgs(['laravel_chat_api.chatModelClass'])
            ->andReturn($this->chatServiceEntityMock);
        $this->serviceEntityFactoryMock->shouldReceive('build')
            ->withArgs([ChatParticipant::class])
            ->andReturn($this->chatParticipantServiceEntityMock);
        $this->dispatcherMock = Mockery::mock(Dispatcher::class);

        $this->connectionMock = Mockery::mock(ConnectionInterface::class);
        $this->connectionMock->shouldReceive('beginTransaction');
        $this->connectionMock->shouldReceive('commit');
        $this->connectionMock->shouldReceive('rollback');

        $this->notificationFactory = Mockery::mock(INotificationsFactory::class);
        $this->chatUserMock = Mockery::mock(IChatUser::class);

        $config = Mockery::mock(Config::class);
        $config->shouldReceive('get')->withAnyArgs()->andReturnUsing(function (string $param) {
            return $param;
        });

        app()->instance('config', $config);

        $this->eventDispatcher = Mockery::mock(EventDispatcher::class);
        $this->eventDispatcher->shouldReceive('dispatch');
        app()->instance('events', $this->eventDispatcher);

        $this->eventDispatcher = Mockery::mock(Translator::class);
        $this->eventDispatcher->shouldReceive('trans');
        $this->eventDispatcher->shouldReceive('get')->andReturn('Mocked tranlation');
        app()->instance('translator', $this->eventDispatcher);
    }

    /**
     * Tests that chat exception will thrown if error in entity service layer.
     *
     * @throws EntityServiceException
     * @throws ChatException
     *
     * @return void
     */
    public function testChatExceptionShouldThrownWhenCreateIfErrorInService(): void
    {
        $this->chatServiceEntityMock->shouldReceive('create')
            ->withAnyArgs()
            ->andThrow(new EntityServiceException());
        $service = new ChatService(
            $this->serviceEntityFactoryMock,
            $this->dispatcherMock,
            $this->connectionMock,
            $this->notificationFactory
        );

        $this->expectException(ChatException::class);

        $this->chatUserMock->shouldReceive('getId')->andReturn(1);
        $service->createChat($this->chatUserMock, [], []);
    }

    /**
     * Tests that service will create chat and all chat participants using chat given users id.
     * Also checks that chat creator will be given creator.
     *
     * @throws EntityServiceException
     * @throws ChatException
     *
     * @return void
     */
    public function testChatWillCreatedWithParticipants(): void
    {
        $creatorId = random_int(0, 1000);
        $this->chatUserMock->shouldReceive('getId')->andReturn($creatorId);

        $chatId = random_int(0, 1000);

        $this->chatServiceEntityMock->shouldReceive('create')
            ->withAnyArgs()
            ->andReturnUsing(function (array $params) use ($chatId) {
                $chat = new Chat($params);
                $chat->id = $chatId;
                return $chat;
            });

        $this->chatParticipantServiceEntityMock->shouldReceive('create')
            ->withArgs([
                [
                    ChatParticipant::USER_ID => $creatorId,
                    ChatParticipant::NOTIFICATION_OFF => false,
                    ChatParticipant::CHAT_ID => $chatId,
                    ChatParticipant::IS_READ => true,
                ]
            ])
            ->andReturn(Mockery::mock(ChatParticipant::class));

        $chatParticipantsCount = 5;

        $chatParticipantsIds = [];

        for ($i = 0; $i < $chatParticipantsCount; $i++) {
            $id = random_int(0, 100);
            $chatParticipantsIds[] = $id;
            $this->chatParticipantServiceEntityMock->shouldReceive('create')
                ->withArgs([
                    [
                        ChatParticipant::USER_ID => $id,
                        ChatParticipant::NOTIFICATION_OFF => false,
                        ChatParticipant::CHAT_ID => $chatId,
                        ChatParticipant::IS_READ => false,
                    ]
                ])
                ->andReturnUsing(function () {
                    $chatUser = Mockery::mock(IChatUser::class);
                    $chatUser->shouldReceive('getId')->andReturn(random_int(0, 1));

                    $chatParticipant = Mockery::mock(ChatParticipant::class);
                    $chatParticipant
                        ->shouldReceive('getUser')
                        ->andReturn($chatUser);

                    return $chatParticipant;
                });
        }

        $service = new ChatService(
            $this->serviceEntityFactoryMock,
            $this->dispatcherMock,
            $this->connectionMock,
            $this->notificationFactory
        );

        $chatData = [
            Chat::NAME => Str::random(),
            Chat::CREATED_BY => $creatorId,
        ];

        $expectedChat = new Chat($chatData);
        $expectedChat->id = $chatId;

        $actualChat = $service->createChat($this->chatUserMock, $chatData, $chatParticipantsIds);

        $this->assertEquals($expectedChat, $actualChat);
    }

    /**
     * Tests that chat can close only who are creator at this moment.
     *
     * @return void
     *
     * @throws ChatException
     * @throws EntityServiceException
     */
    public function testNotCreatorCantCloseChat(): void
    {
        $service = new ChatService(
            $this->serviceEntityFactoryMock,
            $this->dispatcherMock,
            $this->connectionMock,
            $this->notificationFactory
        );

        $actualCreatorId = random_int(0, 1000);
        $wrongCreatorId = random_int($actualCreatorId, 2000);
        $this->chatUserMock->shouldReceive('getId')->andReturn($wrongCreatorId);

        $chat = $this->buildChatMock();
        $chat->shouldReceive('getCreator')->andReturn($this->buildChatUserMock($actualCreatorId));

        $this->expectException(ChatException::class);
        $service->closeChat($this->chatUserMock, $chat);
    }

    /**
     * Tests that can't close chat if it already closed early.
     *
     * @throws ChatException
     * @throws EntityServiceException
     *
     * @return void
     */
    public function testCantCloseAlreadyClosedChat(): void
    {
        $service = new ChatService(
            $this->serviceEntityFactoryMock,
            $this->dispatcherMock,
            $this->connectionMock,
            $this->notificationFactory
        );

        $creatorId = random_int(0, 1000);
        $this->chatUserMock->shouldReceive('getId')->andReturn($creatorId);

        $chat = $this->buildChatMock();
        $chat->shouldReceive('getCreator')->andReturn($this->buildChatUserMock($creatorId));
        $chat->shouldReceive('isClosed')->andReturn(true);

        $this->expectException(ChatException::class);
        $service->closeChat($this->chatUserMock, $chat);
    }

    /**
     * Build chat mock.
     *
     * @return MockInterface|IChat
     */
    protected function buildChatMock(): MockInterface
    {
        $chatId = random_int(0, 1000);
        $chatMock = Mockery::mock(IChat::class);
        $chatMock->shouldReceive('getId')->andReturn($chatId);

        return $chatMock;
    }

    /**
     * Build chat user mock.
     *
     * @param int $id Chat user id to build
     *
     * @return MockInterface|IChatUser
     */
    protected function buildChatUserMock(int $id): MockInterface
    {
        $chatUserMock = Mockery::mock(IChatUser::class);
        $chatUserMock->shouldReceive('getId')->andReturn($id);

        return $chatUserMock;
    }

    /**
     * Build chat participant mock.
     *
     * @return MockInterface|IChatParticipant
     */
    protected function buildChatParticipantMock(): MockInterface
    {
        $chatParticipantMock = Mockery::mock(IChatParticipant::class);
        $chatParticipantMock->shouldReceive('getId')->andReturn(random_int(0, 1000));

        return $chatParticipantMock;
    }
}
