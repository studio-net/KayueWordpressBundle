<?php

namespace Kayue\WordpressBundle\Model;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class CommentManager implements CommentManagerInterface
{
    protected $em;
    protected $repository;
    protected $class;

    function __construct(EntityManager $em, $class = 'Kayue\WordpressBundle\Entity\Comment')
    {
        $this->em = $em;
        $this->repository = $em->getRepository('KayueWordpressBundle:Comment');
        $this->class = $class;
    }

    public function createComment(PostInterface $post, Request $request)
    {
        $class = $this->getClass();

        /**
         * @var $comment Comment
         */
        $comment = new $class();

        $comment->setPost($post);
        $comment->setAuthorIp($request->getClientIp());
        $comment->setAgent($request->headers->get('user-agent', 'Unkown agent'));

        return $comment;
    }

    public function deleteComment(CommentInterface $comment, $andFlush = true)
    {
        $this->em->remove($comment);

        if($andFlush) {
            $this->em->flush();
        }
    }

    public function updateComment(CommentInterface $comment, $andFlush = true)
    {
        $this->em->persist($comment);

        if($andFlush) {
            $this->em->flush();
        }
    }

    public function getClass()
    {
        return $this->class;
    }

    public function findCommentsByPost(PostInterface $post)
    {
        $this->repository->findBy(array(
            'post' => $post
        ));
    }
}