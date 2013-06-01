<?php

namespace Kayue\WordpressBundle\Twig\Extension;

use Doctrine\ORM\EntityManager;
use Kayue\WordpressBundle\Entity\Post;
use Kayue\WordpressBundle\Entity\Taxonomy;
use Kayue\WordpressBundle\Event\SwitchBlogEvent;
use Kayue\WordpressBundle\Model\AttachmentManager;
use Kayue\WordpressBundle\Model\BlogManager;
use Kayue\WordpressBundle\Model\OptionManager;
use Kayue\WordpressBundle\Model\PostManager;
use Kayue\WordpressBundle\Model\PostMetaManager;
use Kayue\WordpressBundle\Model\TermManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WordpressExtension extends \Twig_Extension
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var AttachmentManager
     */
    protected $attachmentManager;

    /**
     * @var BlogManager
     */
    protected $blogManager;

    /**
     * @var OptionManager
     */
    protected $optionManager;

    /**
     * @var PostManager
     */
    protected $postManager;

    /**
     * @var PostMetaManager
     */
    protected $postMetaManager;

    /**
     * @var TermManager
     */
    protected $termManager;

    public function __construct(BlogManager $blogManager)
    {
        $this->blogManager = $blogManager;
        $this->setEntityManager($blogManager->getCurrentBlog()->getEntityManager());
    }

    public function onSwitchBlog(SwitchBlogEvent $event)
    {
        $this->setEntityManager($event->getBlog()->getEntityManager());
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        $this->attachmentManager = new AttachmentManager($em);
        $this->optionManager = new OptionManager($em);
        $this->postManager = new PostManager($em);
        $this->postMetaManager = new PostMetaManager($em);
        $this->termManager = new TermManager();
    }

    public function getName()
    {
        return "wordpress";
    }

    public function getFunctions()
    {
        return array(
            'wp_switch_blog' => new \Twig_Function_Method($this, 'switchBlog'),
            'wp_find_attachments_by_post' => new \Twig_Function_Method($this, 'findAttachmentsByPost'),
            'wp_find_one_attachment_by_id' => new \Twig_Function_Method($this, 'findOneAttachmentById'),
            'wp_find_featured_image_by_post' => new \Twig_Function_Method($this, 'findFeaturedImageByPost'),
            'wp_find_post_thumbnail' => new \Twig_Function_Method($this, 'findFeaturedImageByPost'),
            'wp_find_one_option_by_name' => new \Twig_Function_Method($this, 'findOneOptionByName'),
            'wp_find_one_post_by_id' => new \Twig_Function_Method($this, 'findOnePostById'),
            'wp_find_one_post_by_slug' => new \Twig_Function_Method($this, 'findOnePostBySlug'),
            'wp_find_all_metas_by_post' => new \Twig_Function_Method($this, 'findAllMetasByPost'),
            'wp_find_metas_by' => new \Twig_Function_Method($this, 'findMetasBy'),
            'wp_find_one_meta_by' => new \Twig_Function_Method($this, 'findOneMetaBy'),
            'wp_find_terms_by_post' => new \Twig_Function_Method($this, 'findTermsByPost'),
            'wp_find_categories_by_post' => new \Twig_Function_Method($this, 'findCategoriesByPost'),
            'wp_find_tags_by_post' => new \Twig_Function_Method($this, 'findTagsByPost')
        );
    }

    public function switchBlog($id)
    {
        $this->blogManager->setCurrentBlogId($id);
    }

    public function findAttachmentsByPost(Post $post)
    {
        return $this->attachmentManager->findAttachmentsByPost($post);
    }

    public function findOneAttachmentById($id)
    {
        return $this->attachmentManager->findOneAttachmentById($id);
    }

    public function findFeaturedImageByPost(Post $post, $size = null)
    {
        return $this->attachmentManager->findFeaturedImageByPost($post, $size);
    }

    public function findOneOptionByName($id)
    {
        return $this->optionManager->findOneOptionByName($id);
    }

    public function findOnePostById($id)
    {
        return $this->postManager->findOnePostById($id);
    }

    public function findOnePostBySlug($slug)
    {
        return $this->postManager->findOnePostBySlug($slug);
    }

    public function findAllMetasByPost(Post $post)
    {
        return $this->postMetaManager->findAllMetasByPost($post);
    }

    public function findMetasBy(array $criteria)
    {
        return $this->postMetaManager->findMetasBy($criteria);
    }

    public function findOneMetaBy(array $criteria)
    {
        return $this->postMetaManager->findOneMetaBy($criteria);
    }

    public function findTermsByPost(Post $post, Taxonomy $taxonomy = null)
    {
        return $this->termManager->findTermsByPost($post, $taxonomy);
    }

    public function findCategoriesByPost(Post $post)
    {
        $taxonomy = new Taxonomy();
        $taxonomy->setName('category');
        return $this->findTermsByPost($post, $taxonomy);
    }

    public function findTagsByPost(Post $post)
    {
        $taxonomy = new Taxonomy();
        $taxonomy->setName('post_tag');
        return $this->findTermsByPost($post, $taxonomy);
    }

}