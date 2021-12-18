<?php

use Ynk\Comments\CommentUtils;

class ApiCommentController extends ApiController
{
    /**
     *
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function newComment()
    {
        return Response::json(CommentUtils::create(Input::all()));
    }

    public function checkComment()
    {
        $request = Input::all();

        return Response::json(CommentUtils::checkWithText($request['text'], $request['domain']));
    }

    public function bos()
    {
        return Response::json(array('status' => 'ok'));
    }
} 