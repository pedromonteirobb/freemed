<?php
	// $Id$
	// $Author$

// Class: TeX
//
//	LaTeX rendering class.
//
class TeX {

	var $_buffer; // Internal buffer for creating TeX file
	var $options;

	function TeX ( $options = NULL ) {
		// Pass options to internal array
		if (is_array($options)) {
			$this->options = $options;
		}
	} // end constructor

	function AddLongItem ( $title, $item ) {
		$CRLF = "\n"; 
		$this->_buffer .= '\\section*{\\headingbox{'.
			$this->_SanitizeText($title).'}}'.$CRLF.
			$this->_HTMLToRichText($item).$CRLF.
			$CRLF;
	} // end method AddLongItem

	function AddLongItems ( $items ) {
		if (!is_array($items)) return false;
		foreach ($items AS $title => $item) {
			$this->AddLongItem ( $title, $item );
		}
	} // end method AddLongItems

	function AddShortItems ( $items, $_options = NULL ) {
		$CRLF = "\n"; 
		$this->_buffer .= '\\begin{description}'.$CRLF;
		foreach ($items AS $k => $v) {
			$this->_buffer .= '  \\item[\\headingbox{'.
				$this->_SanitizeText($k).'}] '.
				$this->_SanitizeText($v).$CRLF;
		}
		$this->_buffer .= '\\end{description}'.$CRLF;
		$this->_buffer .= $CRLF;
	} // end method AddShortItems

	function PrintTeX ( $copies = 1 ) {
		$file = $this->RenderToPDF();
		if (!is_object($this->wrapper)) { return false; }
		for ($i=1;$i<=$copies;$i++) {
			$this->wrapper->driver->PrintFile($this->printer, $file);
		}
		return true;
	} // end method Print

	function RenderDebug ( ) {
		$buffer .= $this->_CreateTeXHeader();
		$buffer .= $this->_buffer;
		$buffer .= $this->_CreateTeXFooter();
		print "<pre>\n";
		print $buffer;
		print "</pre>\n";
	} // end method RenderDebug

	// Method: RenderToPDF
	//
	//	Render to PDF and get file name of temporary file
	//
	// Returns:
	//
	//	Name of temporary file.
	//
	function RenderToPDF ( ) {
		$buffer .= $this->_CreateTeXHeader();
		$buffer .= $this->_buffer;
		$buffer .= $this->_CreateTeXFooter();
		
		$tmp = tempnam('/tmp', 'fmtex');

		// Send data to $tmp.ltx
		$fp = fopen ($tmp.'.ltx', 'w');
		fwrite ($fp, $buffer);
		fclose ($fp);

		// Execute pdflatex rendering
		// (twice for appropriate page numbering)
		`pdflatex $tmp.ltx $tmp.pdf`;
		`pdflatex $tmp.ltx $tmp.pdf`;

		// Remove intermediary step file
		unlink($tmp.'.ltx');

		return ($tmp.'.pdf');
	} // end method RenderToPDF

	function SetPrinter ( $wrapper, $printer ) {
		$this->wrapper = $wrapper;
		$this->printer = $printer;
	} // end method SetPrinter

	//----------- Internal Methods -----------------------------------

	function _CreateTeXHeader ( ) {
		$CRLF = "\n";
		return '%%'.$CRLF.
			'%% Output generated by LaTeX Renderer'.$CRLF.
			'%% '.PACKAGENAME.' v'.VERSION.$CRLF.
			'%%'.$CRLF.
			'\\documentclass[10pt,letterpaper]{article}'.$CRLF.
			$CRLF.
			'\\newcommand{\\headingbox}[1]{\\fbox{\\sc #1}}'.$CRLF.
			$CRLF.
			'\\usepackage{courier} % For tt support'.$CRLF.
			'\\usepackage{lastpage}'.$CRLF.
			'\\usepackage{supertabular}'.$CRLF.
			'\\usepackage{fancyhdr}'.$CRLF.
			'\\usepackage[left=0.5in,right=0.5in,top=0.5in,bottom=1.2in,nohead,nofoot]{geometry}'.$CRLF.
			'\\usepackage{relsize}'.$CRLF.
			$CRLF.
			'% Define header and footer'.$CRLF.
			$CRLF.
			'\\lhead{'.$CRLF.
			' \\framebox[\\textwidth]{'.$CRLF.
			'  \\relsize{1}'.$CRLF.
			'  \\begin{tabular*}{\\textwidth}[t]{l@{\\extracolsep{\\fill}}r}'.$CRLF.
			'   \\textbf{'.$this->_SanitizeText(INSTALLATION).'} & '.$CRLF.
			'     '.$this->_HTMLToRichText($this->options['heading']).' \\\\ '.$CRLF.
			'   '.$this->_HTMLToRichText($this->options['title']).' & '.$CRLF.
			'     '.sprintf(__("Page %s of %s"), '\\thepage\\', '\\pageref{LastPage}').' \\\\'.$CRLF.
			'   '.$this->_SanitizeText($this->options['physician']).' & \\today \\\\'.$CRLF.
			'  \\end{tabular*}'.$CRLF.
			' }'.$CRLF.
			'}'.$CRLF.
			$CRLF.
			'\\cfoot{\\textsl{'.PACKAGENAME.' v'.VERSION.'}}'.$CRLF.
			$CRLF.
			'\\renewcommand{\\headrulewidth}{0pt}'.$CRLF.
			'\\renewcommand{\\footrulewidth}{0.5pt}'.$CRLF.
			'\\setlength{\\topskip}{7ex}'.$CRLF.
			'\\setlength{\\headheight}{9ex}'.$CRLF.
			'\\setlength{\\footskip}{3ex}'.$CRLF.
			$CRLF.
			'\\begin{document}'.$CRLF.
			'\\pagestyle{fancy}'.$CRLF.
			$CRLF;
	} // end method _CreateTeXHeader

	function _CreateTeXFooter ( ) {
		$CRLF = "\n";
		return '\\end{document}'.$CRLF.
			$CRLF;
	} // end method _CreateTeXFooter

	// Method: _HTMLToRichText
	//
	//	Convert SGML/HTML formatted "rich text" to LaTeX-formatted
	//	rich text, while sanitizing.
	//
	// Parameters:
	//
	//	$orig - Original marked up string
	//
	// Returns:
	//
	//	LaTeX-style rich text
	function _HTMLToRichText ( $orig ) {
		// Sanitize all but HTML markers
		$text = $this->_SanitizeText($orig, true);

		// Format tags, one by one
		// NOTE ... FIXME!!!!
		//$text = eregi_replace ('<b>[[:alnum:]]<\/b>', '{\\textbf \\1}', $text);
		// Finally, change \n\n into \para and \n into \\
		//$text = str_replace ("\n\n", "\n\\para\n", $text);
		//$text = str_replace ("\n", " \\\\\n", $text);
		
		return $text;
	} // end method _HTMLToRichText

	// Method: _SanitizeText
	//
	//	Escapes offending TeX control sequences.
	//
	// Parameters:
	//
	//	$text - Text to be sanitized
	//
	//	$skip_html - (optional) Whether or not to skip HTML
	//	specific escape sequences. This is useful if presenting
	//	rich text markup (which uses SGML/HTML tags) to the
	//	renderer. Defaults to false.
	//
	// Returns:
	//
	//	Text that can be cleanly inserted into TeX code.
	//
	function _SanitizeText ( $text, $skip_html=false ) {
		$string = $text;

		// First, sanitize escape character
		$string = str_replace('\\', '\\\\', $string);

		// Sanitize {, }
		$string = str_replace('{', '\{', $string);
		$string = str_replace('}', '\}', $string);

		// HTML/SGML specific texts
		if (!$skip_html) {
			$string = str_replace('<', '\<', $string);
			$string = str_replace('>', '\>', $string);
			$string = str_replace('/', '\/', $string);
		}

		// Return processed string
		return $string;
	} // end method _SanitizeText

} // end class TeX

?>
