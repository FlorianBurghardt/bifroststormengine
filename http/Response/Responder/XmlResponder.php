<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Response\Responder;

use de\bifroststormengine\core\Enum\HTTPStatusCode;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\ResponderInterface;
use de\bifroststormengine\http\Response\Response;
use SimpleXMLElement;
#endregion

final class XmlResponder implements ResponderInterface
{
	#region	constructor
	public function __construct(
		private readonly HTTPStatusCode $defaultStatus = HTTPStatusCode::OK,
		private readonly string $rootElement = 'response'
	) {}
	#endregion

	#region	public methods
	public function buildResponse(Request $request, mixed $payload): Response
	{
		if (\is_object($payload) && \method_exists($payload, 'toArray'))
		{
			$payload = $payload->toArray();
		}

		if (!\is_array($payload))
		{
			$payload = ['value' => $payload];
		}

		$xml = new SimpleXMLElement(\sprintf('<%s/>', $this->rootElement));
		$this->arrayToXml($payload, $xml);

		$content = $xml->asXML() ?: '';

		return new Response(
			statusCode: $this->defaultStatus,
			headers: ['Content-Type' => ['application/xml; charset=utf-8']],
			body: $content
		);
	}
	#endregion

	#region	private methods
	private function arrayToXml(array $data, SimpleXMLElement $xml): void
	{
		foreach ($data as $key => $value)
		{
			$elementName = \is_numeric($key) ? 'item' . $key : (string)$key;

			if (\is_array($value))
			{
				$child = $xml->addChild($elementName);
				$this->arrayToXml($value, $child);
			}
			else
			{
				$xml->addChild($elementName, \htmlspecialchars((string)$value));
			}
		}
	}
	#endregion
}