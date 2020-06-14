<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Xin\ApiDoc\Reflection;

use Mpociot\Reflection\DocBlock as BaseDocBlock;
use Mpociot\Reflection\DocBlock\Context;
use Mpociot\Reflection\DocBlock\Description;
use Mpociot\Reflection\DocBlock\Location;

/**
 * Parses the DocBlock for any structure.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class DocBlock extends BaseDocBlock{
	
	/**
	 * Parses the given docblock and populates the member fields.
	 * The constructor may also receive namespace information such as the
	 * current namespace and aliases. This information is used by some tags
	 * (e.g. @param \Reflector|string $docblock A docblock comment (including
	 *     asterisks) or reflector supporting the getDocComment method.
	 *
	 * @param Context  $context The context in which the DocBlock
	 *     occurs.
	 * @param Location $location The location within the file that this
	 *     DocBlock occurs in.
	 * @throws \InvalidArgumentException if the given argument does not have the
	 *     getDocComment method.
	 */
	public function __construct(
		$docblock,
		Context $context = null,
		Location $location = null
	){
		if(is_object($docblock)){
			if(!method_exists($docblock, 'getDocComment')){
				throw new \InvalidArgumentException(
					'Invalid object passed; the given reflector must support '
					.'the getDocComment method'
				);
			}
			
			$docblock = $docblock->getDocComment();
		}
		
		$docblock = $this->cleanInput($docblock);
		
		[$templateMarker, $titleAndDescription, $tags] = $this->splitDocBlock($docblock);
		$this->isTemplateStart = $templateMarker === '#@+';
		$this->isTemplateEnd = $templateMarker === '#@-';
		
		$titleAndDescription = explode("\n", $titleAndDescription, 2);
		$this->short_description = array_shift($titleAndDescription);
		$this->long_description = new Description(array_shift($titleAndDescription), $this);
		
		$this->parseTags($tags);
		
		$this->context = $context;
		$this->location = $location;
	}
	
	/**
	 * Splits the DocBlock into a template marker, summary, description and block of tags.
	 *
	 * @param string $comment Comment to split into the sub-parts.
	 * @return string[] containing the template marker (if any), summary, description and a string containing the tags.
	 * @author Mike van Riel <me@mikevanriel.com> for extending the regex with template marker support.
	 * @author Richard van Velzen (@_richardJ) Special thanks to Richard for the regex responsible for the split.
	 */
	protected function splitDocBlock($comment){
		// Performance improvement cheat: if the first character is an @ then only tags are in this DocBlock. This
		// method does not split tags so we return this verbatim as the fourth result (tags). This saves us the
		// performance impact of running a regular expression
		if(strpos($comment, '@') === 0){
			return ['', '', '', $comment];
		}
		
		// clears all extra horizontal whitespace from the line endings to prevent parsing issues
		$comment = preg_replace('/\h*$/Sum', '', $comment);
		
		/*
		 * Splits the docblock into a template marker, short description, long description and tags section
		 *
		 * - The template marker is empty, #@+ or #@- if the DocBlock starts with either of those (a newline may
		 *   occur after it and will be stripped).
		 * - The short description is started from the first character until a dot is encountered followed by a
		 *   newline OR two consecutive newlines (horizontal whitespace is taken into account to consider spacing
		 *   errors). This is optional.
		 * - The long description, any character until a new line is encountered followed by an @ and word
		 *   characters (a tag). This is optional.
		 * - Tags; the remaining characters
		 *
		 * Big thanks to RichardJ for contributing this Regular Expression
		 */
		preg_match(
			'/
            \A
            # 1. Extract the template marker
            (?:(\#\@\+|\#\@\-)\n?)?

            # 2. Extract the summary
            (?:
              (?! @\pL ) # The summary may not start with an @
              (
                [^\n.]+
                (?:
                  (?! \. \n | \n{2} )     # End summary upon a dot followed by newline or two newlines
                  [\n.] (?! [ \t]* @\pL ) # End summary when an @ is found as first character on a new line
                  [^\n.]+                 # Include anything else
                )*
                \.?
              )?
            )

            # 3. Extract the tags (anything that follows)
            (\s+ [\s\S]*)? # everything that follows
            /ux',
			$comment,
			$matches
		);
		array_shift($matches);
		
		while(count($matches) < 3){
			$matches[] = '';
		}
		
		return $matches;
	}
}
