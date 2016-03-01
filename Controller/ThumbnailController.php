<?php
namespace Kayue\WordpressBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Response;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * This controller will allow us to generate thumbnail on the fly
 *
 * @author Cyril Mizzi <cyril.mizzi@gmail.com>
 */
class ThumbnailController extends Controller {
	/**
	 * Generate the thumbnail format for the given wordpress post id and given
	 * size by GET format. This method will be cached by HTTP Apache
	 *
	 * @param  Symfony\Component\HttpFoundation\Request $request
	 * @return void
	 */
	public function indexAction(Request $request) {
		// Create the JSON response and return it
		$response = new Response();
		$response->setPublic();
		$response->setMaxAge(3600 * 24);
		$response->setSharedMaxAge(6500 * 24);

		// If the cache is almost good, just return it
		if ($response->isNotModified($request)) {
			return $response;
		}

		$post = $this->get("kayue_wordpress")
			->getManager()
			->getRepository("Post")
			->findOneById($request->get("post"));

		$attachment = $this->get("kayue_wordpress.helper.attachment");
		$attachment = $attachment->findThumbnail($post)->getGuid();

		try {
			list($width, $height) = explode("x", $request->get("size"));

			$thumbnail = Image::make($attachment)->resize($width, $height);
			$thumbnail = (string) $thumbnail->encode("jpg");
		} catch (\Exception $e) {
			$thumbnail = "";
		}

		$response->headers->set("Content-Type", "image/jpeg");
		$response->setContent($thumbnail);

		return $response;
	}
}
