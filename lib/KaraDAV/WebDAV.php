<?php

namespace KaraDAV;

use KD2\WebDAV\Server as WebDAV_Server;

class WebDAV extends WebDAV_Server
{
	protected function html_directory(string $uri, iterable $list): ?string
	{
		$out = parent::html_directory($uri, $list);

		if (null !== $out) {
			$options = [
				'wopi_discovery_url' => WOPI_DISCOVERY_URL,
				'server_url' => WWW_URL,
				'webdav_url' => $this->storage->getUserURL(),
				'autosave' => true,
			];

			$uri = $this->storage->getUserURL() . $uri;
			$js = WWW_URL . (DEV ? 'browser/browser.js' : 'browser.min.js');

			$out = str_replace('</head>', sprintf('<script type="text/javascript" src="%s"></script>
				<script type="text/javascript">window.onload = () => browser.init(%s, %s);</script>', $js, json_encode($uri), json_encode($options)), $out);
			$out = str_replace('<body>', '<body><noscript>Please enable javascript</noscript><div style="opacity:0">', $out);
		}

		return $out;
	}

	public function http_options(): void
	{
		parent::http_options();

		if (ACCESS_CONTROL_ALL) {
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Credentials: true');
			header('Access-Control-Allow-Headers: Authorization, *');
			header('Access-Control-Allow-Methods: GET,HEAD,PUT,DELETE,COPY,MOVE,PROPFIND,MKCOL,LOCK,UNLOCK');
		}
	}

	public function log(string $message, ...$params)
	{
		http_log('DAV: ' . $message, ...$params);
	}

	/**
	 * Utility function to create HMAC hash of data, useful for NextCloud and WOPI
	 */
	static public function hmac(array $data, string $key = '')
	{
		$key = SECRET_KEY . sha1($key);
		return parent::hmac($data, $key);
	}
}
