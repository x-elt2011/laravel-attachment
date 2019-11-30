<?php

namespace Xelt2011\Attachment\Contracts;

interface Downloadable
{
	/*
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function download();
}
