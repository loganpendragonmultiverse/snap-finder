@extends('layouts.app', ['title' => 'Chats | Pendragon Chat Bot'])

@section('content')
    <main class="app-grid" data-chat-shell>
        @include('chats.partials.sidebar', [
            'characters' => $characters,
            'chatFolders' => $chatFolders,
            'chats' => $chats,
            'archivedChats' => $archivedChats,
            'activeChat' => $activeChat,
            'modelOptions' => $modelOptions,
            'query' => $query,
        ])

        @include('chats.partials.workspace', [
            'activeChat' => $activeChat,
            'chatFolders' => $chatFolders,
            'modelOptions' => $modelOptions,
            'imageModelOptions' => $imageModelOptions,
            'defaultImageModel' => $defaultImageModel,
            'promptPreview' => $promptPreview,
        ])
    </main>
@endsection
