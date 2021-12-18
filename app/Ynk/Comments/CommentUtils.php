<?php

namespace Ynk\Comments;

class CommentUtils
{
    protected static $fields = array(
        "post_id",
        "post_title",
        "post_url",
        "comment_id",
        "text",
        "username",
        "url"
    );
     public function __construct()
     {
          // NOTE: dursun belki lazım olur..
     }

     /**      
      * @param string  $jsonText permalink of post
      *
      * @return boolean True or false if comment is saved in our database
      */
      
     public static function create($jsonText)
     {
          \DB::beginTransaction();
          foreach(CommentUtils::$fields as $field){
              if(!array_key_exists($field, $jsonText)) {
                  return array('status' => 'failed', 'error' => "$field missing");

              }
          }
          try
          {
                $comment = \Comments::whereRaw("comment_id = ?", array($jsonText['comment_id']))->first();
                if(!$comment)
                {
                    $comment = new \Comments();
                }
                $comment->post_id = $jsonText['post_id'];
                $comment->post_title = $jsonText['post_title'];
                $comment->post_url = $jsonText['post_url'];
                $comment->comment_id =$jsonText['comment_id'];
                $comment->text =$jsonText['text'];
                $comment->username = $jsonText['username'];
                $comment->url = $jsonText['url'];
                $comment->save();

                \DB::commit();

                return array('status' => 'ok');
          }
          catch(Exception $e)
          {
               \DB::rollback();
              return array('status' => 'failed', 'error' => 'db error');
          }
          
          return array('status' => 'failed', 'error' => 'unknown');
     }

     /**
      * @param integer $id id of comment
      * @param array $methods methods of bayes algo's.
      *
      * @throws App::error exception if record not found
      *
      * @return mixed $status array of bayes results
      */
     public static function checkWithId($id, $domain)
     {
        $comment = Comments::where('id', '=', $id)->firstOrFail();

        if($comment)
        {
          $result = array("neutral" => 0, "negative" => 0, "positive" => 0);
          foreach ($methods as $method)
          {
               $bayes_result = \Bayes::check($comment->text, 'comment', $domain);
               foreach ($bayes_result as $key => $value)
               {
                    $result[$key] += round($value/count($methods));
               }
          }
        }
        return $result;
     }


     /**
      * @param string $text text of comment
      * @param array $methods methods of bayes algo's.
      *
      * @return mixed $status array of bayes results
      */
     public static function checkWithText($text,  $domain="default")
     {
          $result = array("neutral" => 0, "negative" => 0, "positive" => 0);
          foreach ($methods as $method)
          {
               $bayes_result = \Bayes::check($text, "comment", $domain);
               foreach ($bayes_result as $key => $value)
               {
                    $result[$key] += round($value/count($methods));
               }
          }

        return $result;
     }

     /**
      * @param integer $id id of comment
      * @param string $status could be published, not published
      *
      * @throw Exception exception if status not one of these "'published', 'not published'"
      */
     public static function update($id, $status)
     {


          DB::beginTransaction();
          try
          {
               // update comment status
               Comments::where('id', '=', $id)->update(array('status' => $status));


               DB::commit();

               return true;
          }
          catch(Exception $e)
          {
               DB::rollback();
          }

          return false;
     }


     /**
      * @param integer $id id of comment
      * @param integer $state state of comment
      *
      * @throw App::error throw exception if comment not found in our database
      *
      * @return array array of result ( array('status' => 'new|old', 'result' => object, 'sentimental' => array()) )
      */

     public function addTraine($id, $state)
     {
        $comment = Comments::findOrFail($id);

        $response = Bayes::learn($comment->text, (int)$state, 'comment', $id);
        $result = Bayes::check($comment->text);
        $response['sentimental'] = $result;

        # add logging
        $user = Auth::user();
        $states = [
           '-1' => 'olumsuz',
            '0' => 'nötr' ,
            '1' => 'olumlu'
        ];

        UserLog::create([
          'user_id' => $user->id,
          'sentimental_id' => $response['result']->id,
          'action' => $states[$state].' olarak eklendi'
        ]);

        return $response;

     }
}

