<?php

namespace Mpdf;

use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

use Mpdf\Conversion;

use Mpdf\Css\Border;
use Mpdf\Css\TextVars;

use Mpdf\Log\Context as LogContext;

use Mpdf\Fonts\MetricsGenerator;

use Mpdf\Output\Destination;

use Mpdf\QrCode;

use Mpdf\Utils\Arrays;
use Mpdf\Utils\NumericString;
use Mpdf\Utils\UtfString;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * mPDF, PHP library generating PDF files from UTF-8 encoded HTML
 *
 * based on FPDF by Olivier Plathey
 *      and HTML2FPDF by Renato Coelho
 *
 * @license GPL-2.0
 */
class Mpdf implements \Psr\Log\LoggerAwareInterface
{

	use Strict;
	use FpdiTrait;

	const VERSION = '8.0.3';

	const SCALE = 72 / 25.4;

	var $useFixedNormalLineHeight; // mPDF 6
	var $useFixedTextBaseline; // mPDF 6
	var $adjustFontDescLineheight; // mPDF 6
	var $interpolateImages; // mPDF 6
	var $defaultPagebreakType; // mPDF 6 pagebreaktype
	var $indexUseSubentries; // mPDF 6

	var $autoScriptToLang; // mPDF 6
	var $baseScript; // mPDF 6
	var $autoVietnamese; // mPDF 6
	var $autoArabic; // mPDF 6

	var $CJKforceend;
	var $h2bookmarks;
	var $h2toc;
	var $decimal_align;
	var $margBuffer;
	var $splitTableBorderWidth;

	var $bookmarkStyles;
	var $useActiveForms;

	var $repackageTTF;
	var $allowCJKorphans;
	var $allowCJKoverflow;

	var $useKerning;
	var $restrictColorSpace;
	var $bleedMargin;
	var $crossMarkMargin;
	var $cropMarkMargin;
	var $cropMarkLength;
	var $nonPrintMargin;

	var $PDFX;
	var $PDFXauto;

	var $PDFA;
	var $PDFAversion = '1-B';
	var $PDFAauto;
	var $ICCProfile;

	var $printers_info;
	var $iterationCounter;
	var $smCapsScale;
	var $smCapsStretch;

	var $backupSubsFont;
	var $backupSIPFont;
	var $fonttrans;
	var $debugfonts;
	var $useAdobeCJK;
	var $percentSubset;
	var $maxTTFFilesize;
	var $BMPonly;

	var $tableMinSizePriority;

	var $dpi;
	var $watermarkImgAlphaBlend;
	var $watermarkImgBehind;
	var $justifyB4br;
	var $packTableData;
	var $pgsIns;
	var $simpleTables;
	var $enableImports;

	var $debug;

	var $setAutoTopMargin;
	var $setAutoBottomMargin;
	var $autoMarginPadding;
	var $collapseBlockMargins;
	var $falseBoldWeight;
	var $normalLineheight;
	var $incrementFPR1;
	var $incrementFPR2;
	var $incrementFPR3;
	var $incrementFPR4;

	var $SHYlang;
	var $SHYleftmin;
	var $SHYrightmin;
	var $SHYcharmin;
	var $SHYcharmax;
	var $SHYlanguages;

	// PageNumber Conditional Text
	var $pagenumPrefix;
	var $pagenumSuffix;

	var $nbpgPrefix;
	var $nbpgSuffix;
	var $showImageErrors;
	var $allow_output_buffering;
	var $autoPadding;
	var $tabSpaces;
	var $autoLangToFont;
	var $watermarkTextAlpha;
	var $watermarkImageAlpha;
	var $watermark_size;
	var $watermark_pos;
	var $annotSize;
	var $annotMargin;
	var $annotOpacity;
	var $title2annots;
	var $keepColumns;
	var $keep_table_proportions;
	var $ignore_table_widths;
	var $ignore_table_percents;
	var $list_number_suffix;

	var $list_auto_mode; // mPDF 6
	var $list_indent_first_level; // mPDF 6
	var $list_indent_default; // mPDF 6
	var $list_indent_default_mpdf;
	var $list_marker_offset; // mPDF 6
	var $list_symbol_size;

	var $useSubstitutions;
	var $CSSselectMedia;

	var $forcePortraitHeaders;
	var $forcePortraitMargins;
	var $displayDefaultOrientation;
	var $ignore_invalid_utf8;
	var $allowedCSStags;
	var $onlyCoreFonts;
	var $allow_charset_conversion;

	var $jSWord;
	var $jSmaxChar;
	var $jSmaxCharLast;
	var $jSmaxWordLast;

	var $max_colH_correction;

	var $table_error_report;
	var $table_error_report_param;
	var $biDirectional;
	var $text_input_as_HTML;
	var $anchor2Bookmark;
	var $shrink_tables_to_fit;

	var $allow_html_optional_endtags;

	var $img_dpi;
	var $whitelistStreamWrappers;

	var $defaultheaderfontsize;
	var $defaultheaderfontstyle;
	var $defaultheaderline;
	var $defaultfooterfontsize;
	var $defaultfooterfontstyle;
	var $defaultfooterline;
	var $header_line_spacing;
	var $footer_line_spacing;

	var $pregCJKchars;
	var $pregRTLchars;
	var $pregCURSchars; // mPDF 6

	var $mirrorMargins;
	var $watermarkText;
	var $watermarkAngle;
	var $watermarkImage;
	var $showWatermarkText;
	var $showWatermarkImage;

	var $svgAutoFont;
	var $svgClasses;

	var $fontsizes;

	var $defaultPageNumStyle; // mPDF 6

	//////////////////////
	// INTERNAL VARIABLES
	//////////////////////
	var $extrapagebreak; // mPDF 6 pagebreaktype

	var $uniqstr; // mPDF 5.7.2
	var $hasOC;

	var $textvar; // mPDF 5.7.1
	var $fontLanguageOverride; // mPDF 5.7.1
	var $OTLtags; // mPDF 5.7.1
	var $OTLdata;  // mPDF 5.7.1

	var $useDictionaryLBR;
	var $useTibetanLBR;

	var $writingToC;
	var $layers;
	var $layerDetails;
	var $current_layer;
	var $open_layer_pane;
	var $decimal_offset;
	var $inMeter;

	var $CJKleading;
	var $CJKfollowing;
	var $CJKoverflow;

	var $textshadow;

	var $colsums;
	var $spanborder;
	var $spanborddet;

	var $visibility;

	var $kerning;
	var $fixedlSpacing;
	var $minwSpacing;
	var $lSpacingCSS;
	var $wSpacingCSS;

	var $spotColorIDs;
	var $SVGcolors;
	var $spotColors;
	var $defTextColor;
	var $defDrawColor;
	var $defFillColor;

	var $tableBackgrounds;
	var $inlineDisplayOff;
	var $kt_y00;
	var $kt_p00;
	var $upperCase;
	var $checkSIP;
	var $checkSMP;
	var $checkCJK;

	var $watermarkImgAlpha;
	var $PDFAXwarnings;

	var $MetadataRoot;
	var $OutputIntentRoot;
	var $InfoRoot;
	var $associatedFilesRoot;

	var $pdf_version;

	private $fontDir;

	var $tempDir;

	var $allowAnnotationFiles;

	var $fontdata;

	var $noImageFile;
	var $lastblockbottommargin;
	var $baselineC;

	// mPDF 5.7.3  inline text-decoration parameters
	var $baselineSup;
	var $baselineSub;
	var $baselineS;
	var $baselineO;

	var $subPos;
	var $subArrMB;
	var $ReqFontStyle;
	var $tableClipPath;

	var $fullImageHeight;

	var $inFixedPosBlock;  // Internal flag for position:fixed block
	var $fixedPosBlock;  // Buffer string for position:fixed block
	var $fixedPosBlockDepth;
	var $fixedPosBlockBBox;
	var $fixedPosBlockSave;
	var $maxPosL;
	var $maxPosR;
	var $loaded;

	var $extraFontSubsets;

	var $docTemplateStart;  // Internal flag for page (page no. -1) that docTemplate starts on

	var $time0;

	var $hyphenationDictionaryFile;

	var $spanbgcolorarray;
	var $default_font;
	var $headerbuffer;
	var $lastblocklevelchange;
	var $nestedtablejustfinished;
	var $linebreakjustfinished;
	var $cell_border_dominance_L;
	var $cell_border_dominance_R;
	var $cell_border_dominance_T;
	var $cell_border_dominance_B;
	var $table_keep_together;
	var $plainCell_properties;
	var $shrin_k1;
	var $outerfilled;

	var $blockContext;
	var $floatDivs;

	var $patterns;
	var $pageBackgrounds;

	var $bodyBackgroundGradient;
	var $bodyBackgroundImage;
	var $bodyBackgroundColor;

	var $writingHTMLheader; // internal flag - used both for writing HTMLHeaders/Footers and FixedPos block
	var $writingHTMLfooter;

	var $angle;

	var $gradients;

	var $kwt_Reference;
	var $kwt_BMoutlines;
	var $kwt_toc;

	var $tbrot_BMoutlines;
	var $tbrot_toc;

	var $col_BMoutlines;
	var $col_toc;

	var $floatbuffer;
	var $floatmargins;

	var $bullet;
	var $bulletarray;

	var $currentLang;
	var $default_lang;

	var $default_available_fonts;

	var $pageTemplate;
	var $docTemplate;
	var $docTemplateContinue;

	var $arabGlyphs;
	var $arabHex;
	var $persianGlyphs;
	var $persianHex;
	var $arabVowels;
	var $arabPrevLink;
	var $arabNextLink;

	var $formobjects; // array of Form Objects for WMF
	var $InlineProperties;
	var $InlineAnnots;
	var $InlineBDF; // mPDF 6 Bidirectional formatting
	var $InlineBDFctr; // mPDF 6

	var $ktAnnots;
	var $tbrot_Annots;
	var $kwt_Annots;
	var $columnAnnots;
	var $columnForms;
	var $tbrotForms;

	var $PageAnnots;

	var $pageDim; // Keep track of page wxh for orientation changes - set in _beginpage, used in _putannots

	var $breakpoints;

	var $tableLevel;
	var $tbctr;
	var $innermostTableLevel;
	var $saveTableCounter;
	var $cellBorderBuffer;

	var $saveHTMLFooter_height;
	var $saveHTMLFooterE_height;

	var $firstPageBoxHeader;
	var $firstPageBoxHeaderEven;
	var $firstPageBoxFooter;
	var $firstPageBoxFooterEven;

	var $page_box;

	var $show_marks; // crop or cross marks
	var $basepathIsLocal;

	var $use_kwt;
	var $kwt;
	var $kwt_height;
	var $kwt_y0;
	var $kwt_x0;
	var $kwt_buffer;
	var $kwt_Links;
	var $kwt_moved;
	var $kwt_saved;

	var $PageNumSubstitutions;

	var $table_borders_separate;
	var $base_table_properties;
	var $borderstyles;

	var $blockjustfinished;

	var $orig_bMargin;
	var $orig_tMargin;
	var $orig_lMargin;
	var $orig_rMargin;
	var $orig_hMargin;
	var $orig_fMargin;

	var $pageHTMLheaders;
	var $pageHTMLfooters;

	var $saveHTMLHeader;
	var $saveHTMLFooter;

	var $HTMLheaderPageLinks;
	var $HTMLheaderPageAnnots;
	var $HTMLheaderPageForms;

	// See Config\FontVariables for these next 5 values
	var $available_unifonts;
	var $sans_fonts;
	var $serif_fonts;
	var $mono_fonts;
	var $defaultSubsFont;

	// List of ALL available CJK fonts (incl. styles) (Adobe add-ons)  hw removed
	var $available_CJK_fonts;

	var $HTMLHeader;
	var $HTMLFooter;
	var $HTMLHeaderE;
	var $HTMLFooterE;
	var $bufferoutput;

	// CJK fonts
	var $Big5_widths;
	var $GB_widths;
	var $SJIS_widths;
	var $UHC_widths;

	// SetProtection
	var $encrypted;

	var $enc_obj_id; // encryption object id

	// Bookmark
	var $BMoutlines;
	var $OutlineRoot;

	// INDEX
	var $ColActive;
	var $Reference;
	var $CurrCol;
	var $NbCol;
	var $y0;   // Top ordinate of columns

	var $ColL;
	var $ColWidth;
	var $ColGap;

	// COLUMNS
	var $ColR;
	var $ChangeColumn;
	var $columnbuffer;
	var $ColDetails;
	var $columnLinks;
	var $colvAlign;

	// Substitutions
	var $substitute;  // Array of substitution strings e.g. <ttz>112</ttz>
	var $entsearch;  // Array of HTML entities (>ASCII 127) to substitute
	var $entsubstitute; // Array of substitution decimal unicode for the Hi entities

	// Default values if no style sheet offered	(cf. http://www.w3.org/TR/CSS21/sample.html)
	var $defaultCSS;
	var $defaultCssFile;

	var $lastoptionaltag; // Save current block item which HTML specifies optionsl endtag
	var $pageoutput;
	var $charset_in;
	var $blk;
	var $blklvl;
	var $ColumnAdjust;

	var $ws; // Word spacing

	var $HREF;
	var $pgwidth;
	var $fontlist;
	var $oldx;
	var $oldy;
	var $B;
	var $I;

	var $tdbegin;
	var $table;
	var $cell;
	var $col;
	var $row;

	var $divbegin;
	var $divwidth;
	var $divheight;
	var $spanbgcolor;

	// mPDF 6 Used for table cell (block-type) properties
	var $cellTextAlign;
	var $cellLineHeight;
	var $cellLineStackingStrategy;
	var $cellLineStackingShift;

	// mPDF 6  Lists
	var $listcounter;
	var $listlvl;
	var $listtype;
	var $listitem;

	var $pjustfinished;
	var $ignorefollowingspaces;
	var $SMALL;
	var $BIG;
	var $dash_on;
	var $dotted_on;

	var $textbuffer;
	var $currentfontstyle;
	var $currentfontfamily;
	var $currentfontsize;
	var $colorarray;
	var $bgcolorarray;
	var $internallink;
	var $enabledtags;

	var $lineheight;
	var $default_lineheight_correction;
	var $basepath;
	var $textparam;

	var $specialcontent;
	var $selectoption;
	var $objectbuffer;

	// Table Rotation
	var $table_rotate;
	var $tbrot_maxw;
	var $tbrot_maxh;
	var $tablebuffer;
	var $tbrot_align;
	var $tbrot_Links;

	var $keep_block_together; // Keep a Block from page-break-inside: avoid

	var $tbrot_y0;
	var $tbrot_x0;
	var $tbrot_w;
	var $tbrot_h;

	var $mb_enc;
	var $originalMbEnc;
	var $originalMbRegexEnc;

	var $directionality;

	var $extgstates; // Used for alpha channel - Transparency (Watermark)
	var $mgl;
	var $mgt;
	var $mgr;
	var $mgb;

	var $tts;
	var $ttz;
	var $tta;

	// Best to alter the below variables using default stylesheet above
	var $page_break_after_avoid;
	var $margin_bottom_collapse;
	var $default_font_size; // in pts
	var $original_default_font_size; // used to save default sizes when using table default
	var $original_default_font;
	var $watermark_font;
	var $defaultAlign;

	// TABLE
	var $defaultTableAlign;
	var $tablethead;
	var $thead_font_weight;
	var $thead_font_style;
	var $thead_font_smCaps;
	var $thead_valign_default;
	var $thead_textalign_default;
	var $tabletfoot;
	var $tfoot_font_weight;
	var $tfoot_font_style;
	var $tfoot_font_smCaps;
	var $tfoot_valign_default;
	var $tfoot_textalign_default;

	var $trow_text_rotate;

	var $cellPaddingL;
	var $cellPaddingR;
	var $cellPaddingT;
	var $cellPaddingB;
	var $table_border_attr_set;
	var $table_border_css_set;

	var $shrin_k; // factor with which to shrink tables - used internally - do not change
	var $shrink_this_table_to_fit; // 0 or false to disable; value (if set) gives maximum factor to reduce fontsize
	var $MarginCorrection; // corrects for OddEven Margins
	var $margin_footer;
	var $margin_header;

	var $tabletheadjustfinished;
	var $usingCoreFont;
	var $charspacing;

	var $js;

	/**
	 * Set timeout for cURL
	 *
	 * @var int
	 */
	var $curlTimeout;

	/**
	 * Set to true to follow redirects with cURL.
	 *
	 * @var bool
	 */
	var $curlFollowLocation;

	/**
	 * Set your own CA certificate store for SSL Certificate verification when using cURL
	 *
	 * Useful setting to use on hosts with outdated CA certificates.
	 *
	 * Download the latest CA certificate from https://curl.haxx.se/docs/caextract.html
	 *
	 * @var string The absolute path to the pem file
	 */
	var $curlCaCertificate;

	/**
	 * Set to true to allow unsafe SSL HTTPS requests.
	 *
	 * Can be useful when using CDN with HTTPS and if you don't want to configure settings with SSL certificates.
	 *
	 * @var bool
	 */
	var $curlAllowUnsafeSslRequests;

	/**
	 * Set the proxy for cURL.
	 *
	 * @see https://curl.haxx.se/libcurl/c/CURLOPT_PROXY.html
	 *
	 * @var string
	 */
	var $curlProxy;

	/**
	 * Set the proxy auth for cURL.
	 *
	 * @see https://curl.haxx.se/libcurl/c/CURLOPT_PROXYUSERPWD.html
	 *
	 * @var string
	 */
	var $curlProxyAuth;

	// Private properties FROM FPDF
	var $DisplayPreferences;
	var $flowingBlockAttr;

	var $page; // current page number

	var $n; // current object number
	var $n_js; // current object number

	var $n_ocg_hidden;
	var $n_ocg_print;
	var $n_ocg_view;

	var $offsets; // array of object offsets
	var $buffer; // buffer holding in-memory PDF
	var $pages; // array containing pages
	var $state; // current document state
	var $compress; // compression flag

	var $DefOrientation; // default orientation
	var $CurOrientation; // current orientation
	var $OrientationChanges; // array indicating orientation changes

	var $fwPt;
	var $fhPt; // dimensions of page format in points
	var $fw;
	var $fh; // dimensions of page format in user unit
	var $wPt;
	var $hPt; // current dimensions of page in points

	var $w;
	var $h; // current dimensions of page in user unit

	var $lMargin; // left margin
	var $tMargin; // top margin
	var $rMargin; // right margin
	var $bMargin; // page break margin
	var $cMarginL; // cell margin Left
	var $cMarginR; // cell margin Right
	var $cMarginT; // cell margin Left
	var $cMarginB; // cell margin Right

	var $DeflMargin; // Default left margin
	var $DefrMargin; // Default right margin

	var $x;
	var $y; // current position in user unit for cell positioning

	var $lasth; // height of last cell printed
	var $LineWidth; // line width in user unit

	var $CoreFonts; // array of standard font names
	var $fonts; // array of used fonts
	var $FontFiles; // array of font files

	var $images; // array of used images
	var $imageVars = []; // array of image vars

	var $PageLinks; // array of links in pages
	var $links; // array of internal links
	var $FontFamily; // current font family
	var $FontStyle; // current font style
	var $CurrentFont; // current font info
	var $FontSizePt; // current font size in points
	var $FontSize; // current font size in user unit
	var $DrawColor; // commands for drawing color
	var $FillColor; // commands for filling color
	var $TextColor; // commands for text color
	var $ColorFlag; // indicates whether fill and text colors are different
	var $autoPageBreak; // automatic page breaking
	var $PageBreakTrigger; // threshold used to trigger page breaks
	var $InFooter; // flag set when processing footer

	var $InHTMLFooter;
	var $processingFooter; // flag set when processing footer - added for columns
	var $processingHeader; // flag set when processing header - added for columns
	var $ZoomMode; // zoom display mode
	var $LayoutMode; // layout display mode
	var $title; // title
	var $subject; // subject
	var $author; // author
	var $keywords; // keywords
	var $creator; // creator

	var $customProperties; // array of custom document properties

	var $associatedFiles; // associated files (see SetAssociatedFiles below)
	var $additionalXmpRdf; // additional rdf added in xmp

	var $aliasNbPg; // alias for total number of pages
	var $aliasNbPgGp; // alias for total number of pages in page group

	var $ispre;
	var $outerblocktags;
	var $innerblocktags;

	/**
	 * @var string
	 */
	private $fontDescriptor;

	/**
	 * @var \Mpdf\Otl
	 */
	private $otl;

	/**
	 * @var \Mpdf\CssManager
	 */
	private $cssManager;

	/**
	 * @var \Mpdf\Gradient
	 */
	private $gradient;

	/**
	 * @var \Mpdf\Image\Bmp
	 */
	private $bmp;

	/**
	 * @var \Mpdf\Image\Wmf
	 */
	private $wmf;

	/**
	 * @var \Mpdf\TableOfContents
	 */
	private $tableOfContents;

	/**
	 * @var \Mpdf\Form
	 */
	private $form;

	/**
	 * @var \Mpdf\DirectWrite
	 */
	private $directWrite;

	/**
	 * @var \Mpdf\Cache
	 */
	private $cache;

	/**
	 * @var \Mpdf\Fonts\FontCache
	 */
	private $fontCache;

	/**
	 * @var \Mpdf\Fonts\FontFileFinder
	 */
	private $fontFileFinder;

	/**
	 * @var \Mpdf\Tag
	 */
	private $tag;

	/**
	 * @var \Mpdf\Barcode
	 * @todo solve Tag dependency and make private
	 */
	public $barcode;

	/**
	 * @var \Mpdf\QrCode\QrCode
	 */
	private $qrcode;

	/**
	 * @var \Mpdf\SizeConverter
	 */
	private $sizeConverter;

	/**
	 * @var \Mpdf\Color\ColorConverter
	 */
	private $colorConverter;

	/**
	 * @var \Mpdf\Color\ColorModeConverter
	 */
	private $colorModeConverter;

	/**
	 * @var \Mpdf\Color\ColorSpaceRestrictor
	 */
	private $colorSpaceRestrictor;

	/**
	 * @var \Mpdf\Hyphenator
	 */
	private $hyphenator;

	/**
	 * @var \Mpdf\Pdf\Protection
	 */
	private $protection;

	/**
	 * @var \Mpdf\RemoteContentFetcher
	 */
	private $remoteContentFetcher;

	/**
	 * @var \Mpdf\Image\ImageProcessor
	 */
	private $imageProcessor;

	/**
	 * @var \Mpdf\Language\LanguageToFontInterface
	 */
	private $languageToFont;

	/**
	 * @var \Mpdf\Language\ScriptToLanguageInterface
	 */
	private $scriptToLanguage;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	private $logger;

	/**
	 * @var \Mpdf\Writer\BaseWriter
	 */
	private $writer;

	/**
	 * @var \Mpdf\Writer\FontWriter
	 */
	private $fontWriter;

	/**
	 * @var \Mpdf\Writer\MetadataWriter
	 */
	private $metadataWriter;

	/**
	 * @var \Mpdf\Writer\ImageWriter
	 */
	private $imageWriter;

	/**
	 * @var \Mpdf\Writer\FormWriter
	 */
	private $formWriter;

	/**
	 * @var \Mpdf\Writer\PageWriter
	 */
	private $pageWriter;

	/**
	 * @var \Mpdf\Writer\BookmarkWriter
	 */
	private $bookmarkWriter;

	/**
	 * @var \Mpdf\Writer\OptionalContentWriter
	 */
	private $optionalContentWriter;

	/**
	 * @var \Mpdf\Writer\ColorWriter
	 */
	private $colorWriter;

	/**
	 * @var \Mpdf\Writer\BackgroundWriter
	 */
	private $backgroundWriter;

	/**
	 * @var \Mpdf\Writer\JavaScriptWriter
	 */
	private $javaScriptWriter;

	/**
	 * @var \Mpdf\Writer\ResourceWriter
	 */
	private $resourceWriter;

	/**
	 * @var string[]
	 */
	private $services;

	/**
	 * @param mixed[] $config
	 */
	public function __construct(array $config = [])
	{
		$this->_dochecks();

		list(
			$mode,
			$format,
			$default_font_size,
			$default_font,
			$mgl,
			$mgr,
			$mgt,
			$mgb,
			$mgh,
			$mgf,
			$orientation
		) = $this->initConstructorParams($config);

		$this->logger = new NullLogger();

		$originalConfig = $config;
		$config = $this->initConfig($originalConfig);

		$serviceFactory = new ServiceFactory();
		$services = $serviceFactory->getServices(
			$this,
			$this->logger,
			$config,
			$this->restrictColorSpace,
			$this->languageToFont,
			$this->scriptToLanguage,
			$this->fontDescriptor,
			$this->bmp,
			$this->directWrite,
			$this->wmf
		);

		$this->services = [];

		foreach ($services as $key => $service) {
			$this->{$key} = $service;
			$this->services[] = $key;
		}

		$this->time0 = microtime(true);

		$this->writingToC = false;

		$this->layers = [];
		$this->current_layer = 0;
		$this->open_layer_pane = false;

		$this->visibility = 'visible';

		$this->tableBackgrounds = [];
		$this->uniqstr = '20110230'; // mPDF 5.7.2
		$this->kt_y00 = '';
		$this->kt_p00 = '';
		$this->BMPonly = [];
		$this->page = 0;
		$this->n = 2;
		$this->buffer = '';
		$this->objectbuffer = [];
		$this->pages = [];
		$this->OrientationChanges = [];
		$this->state = 0;
		$this->fonts = [];
		$this->FontFiles = [];
		$this->images = [];
		$this->links = [];
		$this->InFooter = false;
		$this->processingFooter = false;
		$this->processingHeader = false;
		$this->lasth = 0;
		$this->FontFamily = '';
		$this->FontStyle = '';
		$this->FontSizePt = 9;

		// Small Caps
		$this->inMeter = false;
		$this->decimal_offset = 0;

		$this->PDFAXwarnings = [];

		$this->defTextColor = $this->TextColor = $this->SetTColor($this->colorConverter->convert(0, $this->PDFAXwarnings), true);
		$this->defDrawColor = $this->DrawColor = $this->SetDColor($this->colorConverter->convert(0, $this->PDFAXwarnings), true);
		$this->defFillColor = $this->FillColor = $this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings), true);

		$this->upperCase = require __DIR__ . '/../data/upperCase.php';

		$this->extrapagebreak = true; // mPDF 6 pagebreaktype

		$this->ColorFlag = false;
		$this->extgstates = [];

		$this->mb_enc = 'windows-1252';
		$this->originalMbEnc = mb_internal_encoding();
		$this->originalMbRegexEnc = mb_regex_encoding();

		$this->directionality = 'ltr';
		$this->defaultAlign = 'L';
		$this->defaultTableAlign = 'L';

		$this->fixedPosBlockSave = [];
		$this->extraFontSubsets = 0;

		$this->blockContext = 1;
		$this->floatDivs = [];
		$this->DisplayPreferences = '';

		// Tiling patterns used for backgrounds
		$this->patterns = [];
		$this->pageBackgrounds = [];
		$this->gradients = [];

		// internal flag - used both for writing HTMLHeaders/Footers and FixedPos block
		$this->writingHTMLheader = false;
		// internal flag - used both for writing HTMLHeaders/Footers and FixedPos block
		$this->writingHTMLfooter = false;

		$this->kwt_Reference = [];
		$this->kwt_BMoutlines = [];
		$this->kwt_toc = [];

		$this->tbrot_BMoutlines = [];
		$this->tbrot_toc = [];

		$this->col_BMoutlines = [];
		$this->col_toc = [];

		$this->pgsIns = [];
		$this->PDFAXwarnings = [];
		$this->inlineDisplayOff = false;
		$this->lSpacingCSS = '';
		$this->wSpacingCSS = '';
		$this->fixedlSpacing = false;
		$this->minwSpacing = 0;

		// Baseline for text
		$this->baselineC = 0.35;

		// mPDF 5.7.3  inline text-decoration parameters
		// Sets default change in baseline for <sup> text as factor of preceeding fontsize
		// 0.35 has been recommended; 0.5 matches applications like MS Word
		$this->baselineSup = 0.5;

		// Sets default change in baseline for <sub> text as factor of preceeding fontsize
		$this->baselineSub = -0.2;
		// Sets default height for <strike> text as factor of fontsize
		$this->baselineS = 0.3;
		// Sets default height for overline text as factor of fontsize
		$this->baselineO = 1.1;

		$this->noImageFile = __DIR__ . '/../data/no_image.jpg';
		$this->subPos = 0;

		$this->fullImageHeight = false;
		$this->floatbuffer = [];
		$this->floatmargins = [];
		$this->formobjects = []; // array of Form Objects for WMF
		$this->InlineProperties = [];
		$this->InlineAnnots = [];
		$this->InlineBDF = []; // mPDF 6
		$this->InlineBDFctr = 0; // mPDF 6
		$this->tbrot_Annots = [];
		$this->kwt_Annots = [];
		$this->columnAnnots = [];
		$this->PageLinks = [];
		$this->OrientationChanges = [];
		$this->pageDim = [];
		$this->saveHTMLHeader = [];
		$this->saveHTMLFooter = [];
		$this->PageAnnots = [];
		$this->PageNumSubstitutions = [];
		$this->breakpoints = []; // used in columnbuffer
		$this->tableLevel = 0;
		$this->tbctr = []; // counter for nested tables at each level
		$this->page_box = [];
		$this->show_marks = ''; // crop or cross marks
		$this->kwt = false;
		$this->kwt_height = 0;
		$this->kwt_y0 = 0;
		$this->kwt_x0 = 0;
		$this->kwt_buffer = [];
		$this->kwt_Links = [];
		$this->kwt_moved = false;
		$this->kwt_saved = false;
		$this->PageNumSubstitutions = [];
		$this->base_table_properties = [];
		$this->borderstyles = ['inset', 'groove', 'outset', 'ridge', 'dotted', 'dashed', 'solid', 'double'];
		$this->tbrot_align = 'C';

		$this->pageHTMLheaders = [];
		$this->pageHTMLfooters = [];
		$this->HTMLheaderPageLinks = [];
		$this->HTMLheaderPageAnnots = [];

		$this->HTMLheaderPageForms = [];
		$this->columnForms = [];
		$this->tbrotForms = [];

		$this->pageoutput = [];

		$this->bufferoutput = false;

		$this->encrypted = false;

		$this->BMoutlines = [];
		$this->ColActive = 0;          // Flag indicating that columns are on (the index is being processed)
		$this->Reference = [];    // Array containing the references
		$this->CurrCol = 0;               // Current column number
		$this->ColL = [0];   // Array of Left pos of columns - absolute - needs Margin correction for Odd-Even
		$this->ColR = [0];   // Array of Right pos of columns - absolute pos - needs Margin correction for Odd-Even
		$this->ChangeColumn = 0;
		$this->columnbuffer = [];
		$this->ColDetails = [];  // Keeps track of some column details
		$this->columnLinks = [];  // Cross references PageLinks
		$this->substitute = [];  // Array of substitution strings e.g. <ttz>112</ttz>
		$this->entsearch = [];  // Array of HTML entities (>ASCII 127) to substitute
		$this->entsubstitute = []; // Array of substitution decimal unicode for the Hi entities
		$this->lastoptionaltag = '';
		$this->charset_in = '';
		$this->blk = [];
		$this->blklvl = 0;
		$this->tts = false;
		$this->ttz = false;
		$this->tta = false;
		$this->ispre = false;

		$this->checkSIP = false;
		$this->checkSMP = false;
		$this->checkCJK = false;

		$this->page_break_after_avoid = false;
		$this->margin_bottom_collapse = false;
		$this->tablethead = 0;
		$this->tabletfoot = 0;
		$this->table_border_attr_set = 0;
		$this->table_border_css_set = 0;
		$this->shrin_k = 1.0;
		$this->shrink_this_table_to_fit = 0;
		$this->MarginCorrection = 0;

		$this->tabletheadjustfinished = false;
		$this->usingCoreFont = false;
		$this->charspacing = 0;

		$this->autoPageBreak = true;

		$this->_setPageSize($format, $orientation);
		$this->DefOrientation = $orientation;

		$this->margin_header = $mgh;
		$this->margin_footer = $mgf;

		$bmargin = $mgb;

		$this->DeflMargin = $mgl;
		$this->DefrMargin = $mgr;

		$this->orig_tMargin = $mgt;
		$this->orig_bMargin = $bmargin;
		$this->orig_lMargin = $this->DeflMargin;
		$this->orig_rMargin = $this->DefrMargin;
		$this->orig_hMargin = $this->margin_header;
		$this->orig_fMargin = $this->margin_footer;

		if ($this->setAutoTopMargin == 'pad') {
			$mgt += $this->margin_header;
		}
		if ($this->setAutoBottomMargin == 'pad') {
			$mgb += $this->margin_footer;
		}

		// sets l r t margin
		$this->SetMargins($this->DeflMargin, $this->DefrMargin, $mgt);

		// Automatic page break
		// sets $this->bMargin & PageBreakTrigger
		$this->SetAutoPageBreak($this->autoPageBreak, $bmargin);

		$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;

		// Interior cell margin (1 mm) ? not used
		$this->cMarginL = 1;
		$this->cMarginR = 1;

		// Line width (0.2 mm)
		$this->LineWidth = .567 / Mpdf::SCALE;

		// Enable all tags as default
		$this->DisableTags();
		// Full width display mode
		$this->SetDisplayMode(100); // fullwidth? 'fullpage'

		// Compression
		$this->SetCompression(true);
		// Set default display preferences
		$this->SetDisplayPreferences('');

		$this->initFontConfig($originalConfig);

		// Available fonts
		$this->available_unifonts = [];
		foreach ($this->fontdata as $f => $fs) {
			if (isset($fs['R']) && $fs['R']) {
				$this->available_unifonts[] = $f;
			}
			if (isset($fs['B']) && $fs['B']) {
				$this->available_unifonts[] = $f . 'B';
			}
			if (isset($fs['I']) && $fs['I']) {
				$this->available_unifonts[] = $f . 'I';
			}
			if (isset($fs['BI']) && $fs['BI']) {
				$this->available_unifonts[] = $f . 'BI';
			}
		}

		$this->default_available_fonts = $this->available_unifonts;

		$optcore = false;
		$onlyCoreFonts = false;
		if (preg_match('/([\-+])aCJK/i', $mode, $m)) {
			$mode = preg_replace('/([\-+])aCJK/i', '', $mode); // mPDF 6
			if ($m[1] == '+') {
				$this->useAdobeCJK = true;
			} else {
				$this->useAdobeCJK = false;
			}
		}

		if (strlen($mode) == 1) {
			if ($mode == 's') {
				$this->percentSubset = 100;
				$mode = '';
			} elseif ($mode == 'c') {
				$onlyCoreFonts = true;
				$mode = '';
			}
		} elseif (substr($mode, -2) == '-s') {
			$this->percentSubset = 100;
			$mode = substr($mode, 0, strlen($mode) - 2);
		} elseif (substr($mode, -2) == '-c') {
			$onlyCoreFonts = true;
			$mode = substr($mode, 0, strlen($mode) - 2);
		} elseif (substr($mode, -2) == '-x') {
			$optcore = true;
			$mode = substr($mode, 0, strlen($mode) - 2);
		}

		// Autodetect if mode is a language_country string (en-GB or en_GB or en)
		if ($mode && $mode != 'UTF-8') { // mPDF 6
			list ($coreSuitable, $mpdf_pdf_unifont) = $this->languageToFont->getLanguageOptions($mode, $this->useAdobeCJK);
			if ($coreSuitable && $optcore) {
				$onlyCoreFonts = true;
			}
			if ($mpdf_pdf_unifont) {  // mPDF 6
				$default_font = $mpdf_pdf_unifont;
			}
			$this->currentLang = $mode;
			$this->default_lang = $mode;
		}

		$this->onlyCoreFonts = $onlyCoreFonts;

		if ($this->onlyCoreFonts) {
			$this->setMBencoding('windows-1252'); // sets $this->mb_enc
		} else {
			$this->setMBencoding('UTF-8'); // sets $this->mb_enc
		}
		@mb_regex_encoding('UTF-8'); // required only for mb_ereg... and mb_split functions

		// Adobe CJK fonts
		$this->available_CJK_fonts = [
			'gb',
			'big5',
			'sjis',
			'uhc',
			'gbB',
			'big5B',
			'sjisB',
			'uhcB',
			'gbI',
			'big5I',
			'sjisI',
			'uhcI',
			'gbBI',
			'big5BI',
			'sjisBI',
			'uhcBI',
		];

		// Standard fonts
		$this->CoreFonts = [
			'ccourier' => 'Courier',
			'ccourierB' => 'Courier-Bold',
			'ccourierI' => 'Courier-Oblique',
			'ccourierBI' => 'Courier-BoldOblique',
			'chelvetica' => 'Helvetica',
			'chelveticaB' => 'Helvetica-Bold',
			'chelveticaI' => 'Helvetica-Oblique',
			'chelveticaBI' => 'Helvetica-BoldOblique',
			'ctimes' => 'Times-Roman',
			'ctimesB' => 'Times-Bold',
			'ctimesI' => 'Times-Italic',
			'ctimesBI' => 'Times-BoldItalic',
			'csymbol' => 'Symbol',
			'czapfdingbats' => 'ZapfDingbats'
		];

		$this->fontlist = [
			"ctimes",
			"ccourier",
			"chelvetica",
			"csymbol",
			"czapfdingbats"
		];

		// Substitutions
		$this->setHiEntitySubstitutions();

		if ($this->onlyCoreFonts) {
			$this->useSubstitutions = true;
			$this->SetSubstitutions();
		} else {
			$this->useSubstitutions = $config['useSubstitutions'];
		}

		if (file_exists($this->defaultCssFile)) {
			$css = file_get_contents($this->defaultCssFile);
			$this->cssManager->ReadCSS('<style> ' . $css . ' </style>');
		} else {
			throw new \Mpdf\MpdfException(sprintf('Unable to read default CSS file "%s"', $this->defaultCssFile));
		}

		if ($default_font == '') {
			if ($this->onlyCoreFonts) {
				if (in_array(strtolower($this->defaultCSS['BODY']['FONT-FAMILY']), $this->mono_fonts)) {
					$default_font = 'ccourier';
				} elseif (in_array(strtolower($this->defaultCSS['BODY']['FONT-FAMILY']), $this->sans_fonts)) {
					$default_font = 'chelvetica';
				} else {
					$default_font = 'ctimes';
				}
			} else {
				$default_font = $this->defaultCSS['BODY']['FONT-FAMILY'];
			}
		}
		if (!$default_font_size) {
			$mmsize = $this->sizeConverter->convert($this->defaultCSS['BODY']['FONT-SIZE']);
			$default_font_size = $mmsize * (Mpdf::SCALE);
		}

		if ($default_font) {
			$this->SetDefaultFont($default_font);
		}
		if ($default_font_size) {
			$this->SetDefaultFontSize($default_font_size);
		}

		$this->SetLineHeight(); // lineheight is in mm

		$this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings));
		$this->HREF = '';
		$this->oldy = -1;
		$this->B = 0;
		$this->I = 0;

		// mPDF 6  Lists
		$this->listlvl = 0;
		$this->listtype = [];
		$this->listitem = [];
		$this->listcounter = [];

		$this->tdbegin = false;
		$this->table = [];
		$this->cell = [];
		$this->col = -1;
		$this->row = -1;
		$this->cellBorderBuffer = [];

		$this->divbegin = false;
		// mPDF 6
		$this->cellTextAlign = '';
		$this->cellLineHeight = '';
		$this->cellLineStackingStrategy = '';
		$this->cellLineStackingShift = '';

		$this->divwidth = 0;
		$this->divheight = 0;
		$this->spanbgcolor = false;
		$this->spanborder = false;
		$this->spanborddet = [];

		$this->blockjustfinished = false;
		$this->ignorefollowingspaces = true; // in order to eliminate exceeding left-side spaces
		$this->dash_on = false;
		$this->dotted_on = false;
		$this->textshadow = '';

		$this->currentfontfamily = '';
		$this->currentfontsize = '';
		$this->currentfontstyle = '';
		$this->colorarray = ''; // mPDF 6
		$this->spanbgcolorarray = ''; // mPDF 6
		$this->textbuffer = [];
		$this->internallink = [];
		$this->basepath = "";

		$this->SetBasePath('');

		$this->textparam = [];

		$this->specialcontent = '';
		$this->selectoption = [];
	}

	public function cleanup()
	{
		mb_internal_encoding($this->originalMbEnc);
		@mb_regex_encoding($this->originalMbRegexEnc);
	}

	/**
	 * @param \Psr\Log\LoggerInterface
	 *
	 * @return \Mpdf\Mpdf
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;

		foreach ($this->services as $name) {
			if ($this->$name && $this->$name instanceof \Psr\Log\LoggerAwareInterface) {
				$this->$name->setLogger($logger);
			}
		}

		return $this;
	}

	private function initConfig(array $config)
	{
		$configObject = new ConfigVariables();
		$defaults = $configObject->getDefaults();
		$config = array_intersect_key($config + $defaults, $defaults);

		foreach ($config as $var => $val) {
			$this->{$var} = $val;
		}

		return $config;
	}

	private function initConstructorParams(array $config)
	{
		$constructor = [
			'mode' => '',
			'format' => 'A4',
			'default_font_size' => 0,
			'default_font' => '',
			'margin_left' => 15,
			'margin_right' => 15,
			'margin_top' => 16,
			'margin_bottom' => 16,
			'margin_header' => 9,
			'margin_footer' => 9,
			'orientation' => 'P',
		];

		foreach ($constructor as $key => $val) {
			if (isset($config[$key])) {
				$constructor[$key] = $config[$key];
			}
		}

		return array_values($constructor);
	}

	private function initFontConfig(array $config)
	{
		$configObject = new FontVariables();
		$defaults = $configObject->getDefaults();
		$config = array_intersect_key($config + $defaults, $defaults);
		foreach ($config as $var => $val) {
			$this->{$var} = $val;
		}

		return $config;
	}

	function _setPageSize($format, &$orientation)
	{
		if (is_string($format)) {

			if (empty($format)) {
				$format = 'A4';
			}

			// e.g. A4-L = A4 landscape, A4-P = A4 portrait
			if (preg_match('/([0-9a-zA-Z]*)-([P,L])/i', $format, $m)) {
				$format = $m[1];
				$orientation = $m[2];
			} elseif (empty($orientation)) {
				$orientation = 'P';
			}

			$format = PageFormat::getSizeFromName($format);

			$this->fwPt = $format[0];
			$this->fhPt = $format[1];

		} else {

			if (!$format[0] || !$format[1]) {
				throw new \Mpdf\MpdfException('Invalid page format: ' . $format[0] . ' ' . $format[1]);
			}

			$this->fwPt = $format[0] * Mpdf::SCALE;
			$this->fhPt = $format[1] * Mpdf::SCALE;
		}

		$this->fw = $this->fwPt / Mpdf::SCALE;
		$this->fh = $this->fhPt / Mpdf::SCALE;

		// Page orientation
		$orientation = strtolower($orientation);
		if ($orientation === 'p' || $orientation == 'portrait') {
			$orientation = 'P';
			$this->wPt = $this->fwPt;
			$this->hPt = $this->fhPt;
		} elseif ($orientation === 'l' || $orientation == 'landscape') {
			$orientation = 'L';
			$this->wPt = $this->fhPt;
			$this->hPt = $this->fwPt;
		} else {
			throw new \Mpdf\MpdfException('Incorrect orientation: ' . $orientation);
		}

		$this->CurOrientation = $orientation;

		$this->w = $this->wPt / Mpdf::SCALE;
		$this->h = $this->hPt / Mpdf::SCALE;
	}

	function RestrictUnicodeFonts($res)
	{
		// $res = array of (Unicode) fonts to restrict to: e.g. norasi|norasiB - language specific
		if (count($res)) { // Leave full list of available fonts if passed blank array
			$this->available_unifonts = $res;
		} else {
			$this->available_unifonts = $this->default_available_fonts;
		}
		if (count($this->available_unifonts) == 0) {
			$this->available_unifonts[] = $this->default_available_fonts[0];
		}
		$this->available_unifonts = array_values($this->available_unifonts);
	}

	function setMBencoding($enc)
	{
		if ($this->mb_enc != $enc) {
			$this->mb_enc = $enc;
			mb_internal_encoding($this->mb_enc);
		}
	}

	function SetMargins($left, $right, $top)
	{
		// Set left, top and right margins
		$this->lMargin = $left;
		$this->rMargin = $right;
		$this->tMargin = $top;
	}

	function ResetMargins()
	{
		// ReSet left, top margins
		if (($this->forcePortraitHeaders || $this->forcePortraitMargins) && $this->DefOrientation == 'P' && $this->CurOrientation == 'L') {
			if (($this->mirrorMargins) && (($this->page) % 2 == 0)) { // EVEN
				$this->tMargin = $this->orig_rMargin;
				$this->bMargin = $this->orig_lMargin;
			} else { // ODD	// OR NOT MIRRORING MARGINS/FOOTERS
				$this->tMargin = $this->orig_lMargin;
				$this->bMargin = $this->orig_rMargin;
			}
			$this->lMargin = $this->DeflMargin;
			$this->rMargin = $this->DefrMargin;
			$this->MarginCorrection = 0;
			$this->PageBreakTrigger = $this->h - $this->bMargin;
		} elseif (($this->mirrorMargins) && (($this->page) % 2 == 0)) { // EVEN
			$this->lMargin = $this->DefrMargin;
			$this->rMargin = $this->DeflMargin;
			$this->MarginCorrection = $this->DefrMargin - $this->DeflMargin;
		} else { // ODD	// OR NOT MIRRORING MARGINS/FOOTERS
			$this->lMargin = $this->DeflMargin;
			$this->rMargin = $this->DefrMargin;
			if ($this->mirrorMargins) {
				$this->MarginCorrection = $this->DeflMargin - $this->DefrMargin;
			}
		}
		$this->x = $this->lMargin;
	}

	function SetLeftMargin($margin)
	{
		// Set left margin
		$this->lMargin = $margin;
		if ($this->page > 0 and $this->x < $margin) {
			$this->x = $margin;
		}
	}

	function SetTopMargin($margin)
	{
		// Set top margin
		$this->tMargin = $margin;
	}

	function SetRightMargin($margin)
	{
		// Set right margin
		$this->rMargin = $margin;
	}

	function SetAutoPageBreak($auto, $margin = 0)
	{
		// Set auto page break mode and triggering margin
		$this->autoPageBreak = $auto;
		$this->bMargin = $margin;
		$this->PageBreakTrigger = $this->h - $margin;
	}

	function SetDisplayMode($zoom, $layout = 'continuous')
	{
		$allowedZoomModes = ['fullpage', 'fullwidth', 'real', 'default', 'none'];

		if (in_array($zoom, $allowedZoomModes, true) || is_numeric($zoom)) {
			$this->ZoomMode = $zoom;
		} else {
			throw new \Mpdf\MpdfException('Incorrect zoom display mode: ' . $zoom);
		}

		$allowedLayoutModes = ['single', 'continuous', 'two', 'twoleft', 'tworight', 'default'];

		if (in_array($layout, $allowedLayoutModes, true)) {
			$this->LayoutMode = $layout;
		} else {
			throw new \Mpdf\MpdfException('Incorrect layout display mode: ' . $layout);
		}
	}

	function SetCompression($compress)
	{
		// Set page compression
		if (function_exists('gzcompress')) {
			$this->compress = $compress;
		} else {
			$this->compress = false;
		}
	}

	function SetTitle($title)
	{
		// Title of document // Arrives as UTF-8
		$this->title = $title;
	}

	function SetSubject($subject)
	{
		// Subject of document
		$this->subject = $subject;
	}

	function SetAuthor($author)
	{
		// Author of document
		$this->author = $author;
	}

	function SetKeywords($keywords)
	{
		// Keywords of document
		$this->keywords = $keywords;
	}

	function SetCreator($creator)
	{
		// Creator of document
		$this->creator = $creator;
	}

	function AddCustomProperty($key, $value)
	{
		$this->customProperties[$key] = $value;
	}

	/**
	 * Set one or multiple associated file ("/AF" as required by PDF/A-3)
	 *
	 * param $files is an array of hash containing:
	 *   path: file path on FS
	 *   content: file content
	 *   name: file name (not necessarily the same as the file on FS)
	 *   mime (optional): file mime type (will show up as /Subtype in the PDF)
	 *   description (optional): file description
	 *   AFRelationship (optional): PDF/A-3 AFRelationship (e.g. "Alternative")
	 *
	 * e.g. to associate 1 file:
	 *     [[
	 *         'path' => 'tmp/1234.xml',
	 *         'content' => 'file content',
	 *         'name' => 'public_name.xml',
	 *         'mime' => 'text/xml',
	 *         'description' => 'foo',
	 *         'AFRelationship' => 'Alternative',
	 *     ]]
	 *
	 * @param mixed[] $files Array of arrays of associated files. See above
	 */
	function SetAssociatedFiles(array $files)
	{
		$this->associatedFiles = $files;
	}

	function SetAdditionalXmpRdf($s)
	{
		$this->additionalXmpRdf = $s;
	}

	function SetAnchor2Bookmark($x)
	{
		$this->anchor2Bookmark = $x;
	}

	public function AliasNbPages($alias = '{nb}')
	{
		// Define an alias for total number of pages
		$this->aliasNbPg = $alias;
	}

	public function AliasNbPageGroups($alias = '{nbpg}')
	{
		// Define an alias for total number of pages in a group
		$this->aliasNbPgGp = $alias;
	}

	function SetAlpha($alpha, $bm = 'Normal', $return = false, $mode = 'B')
	{
		// alpha: real value from 0 (transparent) to 1 (opaque)
		// bm:    blend mode, one of the following:
		//          Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn,
		//          HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
		// set alpha for stroking (CA) and non-stroking (ca) operations
		// mode determines F (fill) S (stroke) B (both)
		if (($this->PDFA || $this->PDFX) && $alpha != 1) {
			if (($this->PDFA && !$this->PDFAauto) || ($this->PDFX && !$this->PDFXauto)) {
				$this->PDFAXwarnings[] = "Image opacity must be 100% (Opacity changed to 100%)";
			}
			$alpha = 1;
		}
		$a = ['BM' => '/' . $bm];
		if ($mode == 'F' || $mode == 'B') {
			$a['ca'] = $alpha; // mPDF 5.7.2
		}
		if ($mode == 'S' || $mode == 'B') {
			$a['CA'] = $alpha; // mPDF 5.7.2
		}
		$gs = $this->AddExtGState($a);
		if ($return) {
			return sprintf('/GS%d gs', $gs);
		} else {
			$this->writer->write(sprintf('/GS%d gs', $gs));
		}
	}

	function AddExtGState($parms)
	{
		$n = count($this->extgstates);
		// check if graphics state already exists
		for ($i = 1; $i <= $n; $i++) {
			if (count($this->extgstates[$i]['parms']) == count($parms)) {
				$same = true;
				foreach ($this->extgstates[$i]['parms'] as $k => $v) {
					if (!isset($parms[$k]) || $parms[$k] != $v) {
						$same = false;
						break;
					}
				}
				if ($same) {
					return $i;
				}
			}
		}
		$n++;
		$this->extgstates[$n]['parms'] = $parms;
		return $n;
	}

	function SetVisibility($v)
	{
		if (($this->PDFA || $this->PDFX) && $this->visibility != 'visible') {
			$this->PDFAXwarnings[] = "Cannot set visibility to anything other than full when using PDFA or PDFX";
			return '';
		} elseif (!$this->PDFA && !$this->PDFX) {
			$this->pdf_version = '1.5';
		}
		if ($this->visibility != 'visible') {
			$this->writer->write('EMC');
			$this->hasOC = intval($this->hasOC);
		}
		if ($v == 'printonly') {
			$this->writer->write('/OC /OC1 BDC');
			$this->hasOC = ($this->hasOC | 1);
		} elseif ($v == 'screenonly') {
			$this->writer->write('/OC /OC2 BDC');
			$this->hasOC = ($this->hasOC | 2);
		} elseif ($v == 'hidden') {
			$this->writer->write('/OC /OC3 BDC');
			$this->hasOC = ($this->hasOC | 4);
		} elseif ($v != 'visible') {
			throw new \Mpdf\MpdfException('Incorrect visibility: ' . $v);
		}
		$this->visibility = $v;
	}

	function Open()
	{
		// Begin document
		if ($this->state == 0) {
			// Was is function _begindoc()
			// Start document
			$this->state = 1;
			$this->writer->write('%PDF-' . $this->pdf_version);
			$this->writer->write('%' . chr(226) . chr(227) . chr(207) . chr(211)); // 4 chars > 128 to show binary file
		}
	}

	function Close()
	{
		// @log Closing last page

		// Terminate document
		if ($this->state == 3) {
			return;
		}
		if ($this->page == 0) {
			$this->AddPage($this->CurOrientation);
		}
		if (count($this->cellBorderBuffer)) {
			$this->printcellbuffer();
		} // *TABLES*
		if ($this->tablebuffer) {
			$this->printtablebuffer();
		} // *TABLES*
		/* -- COLUMNS -- */

		if ($this->ColActive) {
			$this->SetColumns(0);
			$this->ColActive = 0;
			if (count($this->columnbuffer)) {
				$this->printcolumnbuffer();
			}
		}
		/* -- END COLUMNS -- */

		// BODY Backgrounds
		$s = '';

		$s .= $this->PrintBodyBackgrounds();
		$s .= $this->PrintPageBackgrounds();

		$this->pages[$this->page] = preg_replace(
			'/(___BACKGROUND___PATTERNS' . $this->uniqstr . ')/',
			"\n" . $s . "\n" . '\\1',
			$this->pages[$this->page]
		);

		$this->pageBackgrounds = [];

		if ($this->visibility != 'visible') {
			$this->SetVisibility('visible');
		}

		$this->EndLayer();

		if (!$this->tableOfContents->TOCmark) { // Page footer
			$this->InFooter = true;
			$this->Footer();
			$this->InFooter = false;
		}

		if ($this->tableOfContents->TOCmark || count($this->tableOfContents->m_TOC)) {
			$this->tableOfContents->insertTOC();
		}

		// *TOC*
		// Close page
		$this->_endpage();

		// Close document
		$this->_enddoc();
	}

	/* -- BACKGROUNDS -- */

	function _resizeBackgroundImage($imw, $imh, $cw, $ch, $resize, $repx, $repy, $pba = [], $size = [])
	{
		// pba is background positioning area (from CSS background-origin) may not always be set [x,y,w,h]
		// size is from CSS3 background-size - takes precendence over old resize
		// $w - absolute length or % or auto or cover | contain
		// $h - absolute length or % or auto or cover | contain
		if (isset($pba['w'])) {
			$cw = $pba['w'];
		}
		if (isset($pba['h'])) {
			$ch = $pba['h'];
		}

		$cw = $cw * Mpdf::SCALE;
		$ch = $ch * Mpdf::SCALE;
		if (empty($size) && !$resize) {
			return [$imw, $imh, $repx, $repy];
		}

		if (isset($size['w']) && $size['w']) {
			if ($size['w'] == 'contain') {
				// Scale the image, while preserving its intrinsic aspect ratio (if any),
				// to the largest size such that both its width and its height can fit inside the background positioning area.
				// Same as resize==3
				$h = $imh * $cw / $imw;
				$w = $cw;
				if ($h > $ch) {
					$w = $w * $ch / $h;
					$h = $ch;
				}
			} elseif ($size['w'] == 'cover') {
				// Scale the image, while preserving its intrinsic aspect ratio (if any),
				// to the smallest size such that both its width and its height can completely cover the background positioning area.
				$h = $imh * $cw / $imw;
				$w = $cw;
				if ($h < $ch) {
					$w = $w * $h / $ch;
					$h = $ch;
				}
			} else {
				if (stristr($size['w'], '%')) {
					$size['w'] = (float) $size['w'];
					$size['w'] /= 100;
					$size['w'] = ($cw * $size['w']);
				}
				if (stristr($size['h'], '%')) {
					$size['h'] = (float) $size['h'];
					$size['h'] /= 100;
					$size['h'] = ($ch * $size['h']);
				}
				if ($size['w'] == 'auto' && $size['h'] == 'auto') {
					$w = $imw;
					$h = $imh;
				} elseif ($size['w'] == 'auto' && $size['h'] != 'auto') {
					$w = $imw * $size['h'] / $imh;
					$h = $size['h'];
				} elseif ($size['w'] != 'auto' && $size['h'] == 'auto') {
					$h = $imh * $size['w'] / $imw;
					$w = $size['w'];
				} else {
					$w = $size['w'];
					$h = $size['h'];
				}
			}
			return [$w, $h, $repx, $repy];
		} elseif ($resize == 1 && $imw > $cw) {
			$h = $imh * $cw / $imw;
			return [$cw, $h, $repx, $repy];
		} elseif ($resize == 2 && $imh > $ch) {
			$w = $imw * $ch / $imh;
			return [$w, $ch, $repx, $repy];
		} elseif ($resize == 3) {
			$w = $imw;
			$h = $imh;
			if ($w > $cw) {
				$h = $h * $cw / $w;
				$w = $cw;
			}
			if ($h > $ch) {
				$w = $w * $ch / $h;
				$h = $ch;
			}
			return [$w, $h, $repx, $repy];
		} elseif ($resize == 4) {
			$h = $imh * $cw / $imw;
			return [$cw, $h, $repx, $repy];
		} elseif ($resize == 5) {
			$w = $imw * $ch / $imh;
			return [$w, $ch, $repx, $repy];
		} elseif ($resize == 6) {
			return [$cw, $ch, $repx, $repy];
		}
		return [$imw, $imh, $repx, $repy];
	}

	function SetBackground(&$properties, &$maxwidth)
	{
		if (isset($properties['BACKGROUND-ORIGIN']) && ($properties['BACKGROUND-ORIGIN'] == 'border-box' || $properties['BACKGROUND-ORIGIN'] == 'content-box')) {
			$origin = $properties['BACKGROUND-ORIGIN'];
		} else {
			$origin = 'padding-box';
		}

		if (isset($properties['BACKGROUND-SIZE'])) {
			if (stristr($properties['BACKGROUND-SIZE'], 'contain')) {
				$bsw = $bsh = 'contain';
			} elseif (stristr($properties['BACKGROUND-SIZE'], 'cover')) {
				$bsw = $bsh = 'cover';
			} else {
				$bsw = $bsh = 'auto';
				$sz = preg_split('/\s+/', trim($properties['BACKGROUND-SIZE']));
				if (count($sz) == 2) {
					$bsw = $sz[0];
					$bsh = $sz[1];
				} else {
					$bsw = $sz[0];
				}
				if (!stristr($bsw, '%') && !stristr($bsw, 'auto')) {
					$bsw = $this->sizeConverter->convert($bsw, $maxwidth, $this->FontSize);
				}
				if (!stristr($bsh, '%') && !stristr($bsh, 'auto')) {
					$bsh = $this->sizeConverter->convert($bsh, $maxwidth, $this->FontSize);
				}
			}
			$size = ['w' => $bsw, 'h' => $bsh];
		} else {
			$size = false;
		} // mPDF 6
		if (preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient/', $properties['BACKGROUND-IMAGE'])) {
			return ['gradient' => $properties['BACKGROUND-IMAGE'], 'origin' => $origin, 'size' => $size];
		} else {
			$file = $properties['BACKGROUND-IMAGE'];
			$sizesarray = $this->Image($file, 0, 0, 0, 0, '', '', false, false, false, false, true);
			if (isset($sizesarray['IMAGE_ID'])) {
				$image_id = $sizesarray['IMAGE_ID'];
				$orig_w = $sizesarray['WIDTH'] * Mpdf::SCALE;  // in user units i.e. mm
				$orig_h = $sizesarray['HEIGHT'] * Mpdf::SCALE;  // (using $this->img_dpi)
				if (isset($properties['BACKGROUND-IMAGE-RESOLUTION'])) {
					if (preg_match('/from-image/i', $properties['BACKGROUND-IMAGE-RESOLUTION']) && isset($sizesarray['set-dpi']) && $sizesarray['set-dpi'] > 0) {
						$orig_w *= $this->img_dpi / $sizesarray['set-dpi'];
						$orig_h *= $this->img_dpi / $sizesarray['set-dpi'];
					} elseif (preg_match('/(\d+)dpi/i', $properties['BACKGROUND-IMAGE-RESOLUTION'], $m)) {
						$dpi = $m[1];
						if ($dpi > 0) {
							$orig_w *= $this->img_dpi / $dpi;
							$orig_h *= $this->img_dpi / $dpi;
						}
					}
				}
				$x_repeat = true;
				$y_repeat = true;
				if (isset($properties['BACKGROUND-REPEAT'])) {
					if ($properties['BACKGROUND-REPEAT'] == 'no-repeat' || $properties['BACKGROUND-REPEAT'] == 'repeat-x') {
						$y_repeat = false;
					}
					if ($properties['BACKGROUND-REPEAT'] == 'no-repeat' || $properties['BACKGROUND-REPEAT'] == 'repeat-y') {
						$x_repeat = false;
					}
				}
				$x_pos = 0;
				$y_pos = 0;
				if (isset($properties['BACKGROUND-POSITION'])) {
					$ppos = preg_split('/\s+/', $properties['BACKGROUND-POSITION']);
					$x_pos = $ppos[0];
					$y_pos = $ppos[1];
					if (!stristr($x_pos, '%')) {
						$x_pos = $this->sizeConverter->convert($x_pos, $maxwidth, $this->FontSize);
					}
					if (!stristr($y_pos, '%')) {
						$y_pos = $this->sizeConverter->convert($y_pos, $maxwidth, $this->FontSize);
					}
				}
				if (isset($properties['BACKGROUND-IMAGE-RESIZE'])) {
					$resize = $properties['BACKGROUND-IMAGE-RESIZE'];
				} else {
					$resize = 0;
				}
				if (isset($properties['BACKGROUND-IMAGE-OPACITY'])) {
					$opacity = $properties['BACKGROUND-IMAGE-OPACITY'];
				} else {
					$opacity = 1;
				}
				return ['image_id' => $image_id, 'orig_w' => $orig_w, 'orig_h' => $orig_h, 'x_pos' => $x_pos, 'y_pos' => $y_pos, 'x_repeat' => $x_repeat, 'y_repeat' => $y_repeat, 'resize' => $resize, 'opacity' => $opacity, 'itype' => $sizesarray['itype'], 'origin' => $origin, 'size' => $size];
			}
		}
		return false;
	}

	/* -- END BACKGROUNDS -- */

	function PrintBodyBackgrounds()
	{
		$s = '';
		$clx = 0;
		$cly = 0;
		$clw = $this->w;
		$clh = $this->h;
		// If using bleed and trim margins in paged media
		if ($this->pageDim[$this->page]['outer_width_LR'] || $this->pageDim[$this->page]['outer_width_TB']) {
			$clx = $this->pageDim[$this->page]['outer_width_LR'] - $this->pageDim[$this->page]['bleedMargin'];
			$cly = $this->pageDim[$this->page]['outer_width_TB'] - $this->pageDim[$this->page]['bleedMargin'];
			$clw = $this->w - 2 * $clx;
			$clh = $this->h - 2 * $cly;
		}

		if ($this->bodyBackgroundColor) {
			$s .= 'q ' . $this->SetFColor($this->bodyBackgroundColor, true) . "\n";
			if ($this->bodyBackgroundColor[0] == 5) { // RGBa
				$s .= $this->SetAlpha(ord($this->bodyBackgroundColor[4]) / 100, 'Normal', true, 'F') . "\n";
			} elseif ($this->bodyBackgroundColor[0] == 6) { // CMYKa
				$s .= $this->SetAlpha(ord($this->bodyBackgroundColor[5]) / 100, 'Normal', true, 'F') . "\n";
			}
			$s .= sprintf('%.3F %.3F %.3F %.3F re f Q', ($clx * Mpdf::SCALE), ($cly * Mpdf::SCALE), $clw * Mpdf::SCALE, $clh * Mpdf::SCALE) . "\n";
		}

		/* -- BACKGROUNDS -- */
		if ($this->bodyBackgroundGradient) {
			$g = $this->gradient->parseBackgroundGradient($this->bodyBackgroundGradient);
			if ($g) {
				$s .= $this->gradient->Gradient($clx, $cly, $clw, $clh, (isset($g['gradtype']) ? $g['gradtype'] : null), $g['stops'], $g['colorspace'], $g['coords'], $g['extend'], true);
			}
		}
		if ($this->bodyBackgroundImage) {
			if (isset($this->bodyBackgroundImage['gradient']) && $this->bodyBackgroundImage['gradient'] && preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient/', $this->bodyBackgroundImage['gradient'])) {
				$g = $this->gradient->parseMozGradient($this->bodyBackgroundImage['gradient']);
				if ($g) {
					$s .= $this->gradient->Gradient($clx, $cly, $clw, $clh, $g['type'], $g['stops'], $g['colorspace'], $g['coords'], $g['extend'], true);
				}
			} elseif ($this->bodyBackgroundImage['image_id']) { // Background pattern
				$n = count($this->patterns) + 1;
				// If using resize, uses TrimBox (not including the bleed)
				list($orig_w, $orig_h, $x_repeat, $y_repeat) = $this->_resizeBackgroundImage($this->bodyBackgroundImage['orig_w'], $this->bodyBackgroundImage['orig_h'], $clw, $clh, $this->bodyBackgroundImage['resize'], $this->bodyBackgroundImage['x_repeat'], $this->bodyBackgroundImage['y_repeat']);

				$this->patterns[$n] = ['x' => $clx, 'y' => $cly, 'w' => $clw, 'h' => $clh, 'pgh' => $this->h, 'image_id' => $this->bodyBackgroundImage['image_id'], 'orig_w' => $orig_w, 'orig_h' => $orig_h, 'x_pos' => $this->bodyBackgroundImage['x_pos'], 'y_pos' => $this->bodyBackgroundImage['y_pos'], 'x_repeat' => $x_repeat, 'y_repeat' => $y_repeat, 'itype' => $this->bodyBackgroundImage['itype']];
				if (($this->bodyBackgroundImage['opacity'] > 0 || $this->bodyBackgroundImage['opacity'] === '0') && $this->bodyBackgroundImage['opacity'] < 1) {
					$opac = $this->SetAlpha($this->bodyBackgroundImage['opacity'], 'Normal', true);
				} else {
					$opac = '';
				}
				$s .= sprintf('q /Pattern cs /P%d scn %s %.3F %.3F %.3F %.3F re f Q', $n, $opac, ($clx * Mpdf::SCALE), ($cly * Mpdf::SCALE), $clw * Mpdf::SCALE, $clh * Mpdf::SCALE) . "\n";
			}
		}
		/* -- END BACKGROUNDS -- */
		return $s;
	}

	function _setClippingPath($clx, $cly, $clw, $clh)
	{
		$s = ' q 0 w '; // Line width=0
		$s .= sprintf('%.3F %.3F m ', ($clx) * Mpdf::SCALE, ($this->h - ($cly)) * Mpdf::SCALE); // start point TL before the arc
		$s .= sprintf('%.3F %.3F l ', ($clx) * Mpdf::SCALE, ($this->h - ($cly + $clh)) * Mpdf::SCALE); // line to BL
		$s .= sprintf('%.3F %.3F l ', ($clx + $clw) * Mpdf::SCALE, ($this->h - ($cly + $clh)) * Mpdf::SCALE); // line to BR
		$s .= sprintf('%.3F %.3F l ', ($clx + $clw) * Mpdf::SCALE, ($this->h - ($cly)) * Mpdf::SCALE); // line to TR
		$s .= sprintf('%.3F %.3F l ', ($clx) * Mpdf::SCALE, ($this->h - ($cly)) * Mpdf::SCALE); // line to TL
		$s .= ' W n '; // Ends path no-op & Sets the clipping path
		return $s;
	}

	function PrintPageBackgrounds($adjustmenty = 0)
	{
		$s = '';

		ksort($this->pageBackgrounds);

		foreach ($this->pageBackgrounds as $bl => $pbs) {

			foreach ($pbs as $pb) {

				if ((!isset($pb['image_id']) && !isset($pb['gradient'])) || isset($pb['shadowonly'])) { // Background colour or boxshadow

					if ($pb['z-index'] > 0) {
						$this->current_layer = $pb['z-index'];
						$s .= "\n" . '/OCBZ-index /ZI' . $pb['z-index'] . ' BDC' . "\n";
					}

					if ($pb['visibility'] != 'visible') {
						if ($pb['visibility'] == 'printonly') {
							$s .= '/OC /OC1 BDC' . "\n";
						} elseif ($pb['visibility'] == 'screenonly') {
							$s .= '/OC /OC2 BDC' . "\n";
						} elseif ($pb['visibility'] == 'hidden') {
							$s .= '/OC /OC3 BDC' . "\n";
						}
					}

					// Box shadow
					if (isset($pb['shadow']) && $pb['shadow']) {
						$s .= $pb['shadow'] . "\n";
					}

					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= $pb['clippath'] . "\n";
					}

					$s .= 'q ' . $this->SetFColor($pb['col'], true) . "\n";

					if ($pb['col'] && $pb['col'][0] === '5') { // RGBa
						$s .= $this->SetAlpha(ord($pb['col'][4]) / 100, 'Normal', true, 'F') . "\n";
					} elseif ($pb['col'] && $pb['col'][0] === '6') { // CMYKa
						$s .= $this->SetAlpha(ord($pb['col'][5]) / 100, 'Normal', true, 'F') . "\n";
					}

					$s .= sprintf('%.3F %.3F %.3F %.3F re f Q', $pb['x'] * Mpdf::SCALE, ($this->h - $pb['y']) * Mpdf::SCALE, $pb['w'] * Mpdf::SCALE, -$pb['h'] * Mpdf::SCALE) . "\n";

					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= 'Q' . "\n";
					}

					if ($pb['visibility'] != 'visible') {
						$s .= 'EMC' . "\n";
					}

					if ($pb['z-index'] > 0) {
						$s .= "\n" . 'EMCBZ-index' . "\n";
						$this->current_layer = 0;
					}
				}
			}

			/* -- BACKGROUNDS -- */
			foreach ($pbs as $pb) {

				if ((isset($pb['gradient']) && $pb['gradient']) || (isset($pb['image_id']) && $pb['image_id'])) {

					if ($pb['z-index'] > 0) {
						$this->current_layer = $pb['z-index'];
						$s .= "\n" . '/OCGZ-index /ZI' . $pb['z-index'] . ' BDC' . "\n";
					}

					if ($pb['visibility'] != 'visible') {
						if ($pb['visibility'] == 'printonly') {
							$s .= '/OC /OC1 BDC' . "\n";
						} elseif ($pb['visibility'] == 'screenonly') {
							$s .= '/OC /OC2 BDC' . "\n";
						} elseif ($pb['visibility'] == 'hidden') {
							$s .= '/OC /OC3 BDC' . "\n";
						}
					}

				}

				if (isset($pb['gradient']) && $pb['gradient']) {

					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= $pb['clippath'] . "\n";
					}

					$s .= $this->gradient->Gradient($pb['x'], $pb['y'], $pb['w'], $pb['h'], $pb['gradtype'], $pb['stops'], $pb['colorspace'], $pb['coords'], $pb['extend'], true);

					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= 'Q' . "\n";
					}

				} elseif (isset($pb['image_id']) && $pb['image_id']) { // Background Image

					$pb['y'] -= $adjustmenty;
					$pb['h'] += $adjustmenty;
					$n = count($this->patterns) + 1;

					list($orig_w, $orig_h, $x_repeat, $y_repeat) = $this->_resizeBackgroundImage($pb['orig_w'], $pb['orig_h'], $pb['w'], $pb['h'], $pb['resize'], $pb['x_repeat'], $pb['y_repeat'], $pb['bpa'], $pb['size']);

					$this->patterns[$n] = ['x' => $pb['x'], 'y' => $pb['y'], 'w' => $pb['w'], 'h' => $pb['h'], 'pgh' => $this->h, 'image_id' => $pb['image_id'], 'orig_w' => $orig_w, 'orig_h' => $orig_h, 'x_pos' => $pb['x_pos'], 'y_pos' => $pb['y_pos'], 'x_repeat' => $x_repeat, 'y_repeat' => $y_repeat, 'itype' => $pb['itype'], 'bpa' => $pb['bpa']];

					$x = $pb['x'] * Mpdf::SCALE;
					$y = ($this->h - $pb['y']) * Mpdf::SCALE;
					$w = $pb['w'] * Mpdf::SCALE;
					$h = -$pb['h'] * Mpdf::SCALE;

					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= $pb['clippath'] . "\n";
					}

					if ($this->writingHTMLfooter || $this->writingHTMLheader) { // Write each (tiles) image rather than use as a pattern

						$iw = $pb['orig_w'] / Mpdf::SCALE;
						$ih = $pb['orig_h'] / Mpdf::SCALE;

						$w = $pb['w'];
						$h = $pb['h'];
						$x0 = $pb['x'];
						$y0 = $pb['y'];

						if (isset($pb['bpa']) && $pb['bpa']) {
							$w = $pb['bpa']['w'];
							$h = $pb['bpa']['h'];
							$x0 = $pb['bpa']['x'];
							$y0 = $pb['bpa']['y'];
						}

						if (isset($pb['size']['w']) && $pb['size']['w']) {
							$size = $pb['size'];

							if ($size['w'] == 'contain') {
								// Scale the image, while preserving its intrinsic aspect ratio (if any), to the largest
								// size such that both its width and its height can fit inside the background positioning area.
								// Same as resize==3
								$ih = $ih * $pb['bpa']['w'] / $iw;
								$iw = $pb['bpa']['w'];
								if ($ih > $pb['bpa']['h']) {
									$iw = $iw * $pb['bpa']['h'] / $ih;
									$ih = $pb['bpa']['h'];
								}
							} elseif ($size['w'] == 'cover') {
								// Scale the image, while preserving its intrinsic aspect ratio (if any), to the smallest
								// size such that both its width and its height can completely cover the background positioning area.
								$ih = $ih * $pb['bpa']['w'] / $iw;
								$iw = $pb['bpa']['w'];
								if ($ih < $pb['bpa']['h']) {
									$iw = $iw * $ih / $pb['bpa']['h'];
									$ih = $pb['bpa']['h'];
								}
							} else {

								if (NumericString::containsPercentChar($size['w'])) {
									$size['w'] = NumericString::removePercentChar($size['w']);
									$size['w'] /= 100;
									$size['w'] = ($pb['bpa']['w'] * $size['w']);
								}

								if (NumericString::containsPercentChar($size['h'])) {
									$size['h'] = NumericString::removePercentChar($size['h']);
									$size['h'] /= 100;
									$size['h'] = ($pb['bpa']['h'] * $size['h']);
								}

								if ($size['w'] == 'auto' && $size['h'] == 'auto') {
									$iw = $iw;
									$ih = $ih;
								} elseif ($size['w'] == 'auto' && $size['h'] != 'auto') {
									$iw = $iw * $size['h'] / $ih;
									$ih = $size['h'];
								} elseif ($size['w'] != 'auto' && $size['h'] == 'auto') {
									$ih = $ih * $size['w'] / $iw;
									$iw = $size['w'];
								} else {
									$iw = $size['w'];
									$ih = $size['h'];
								}
							}
						}

						// Number to repeat
						if ($pb['x_repeat']) {
							$nx = ceil($pb['w'] / $iw) + 1;
						} else {
							$nx = 1;
						}

						if ($pb['y_repeat']) {
							$ny = ceil($pb['h'] / $ih) + 1;
						} else {
							$ny = 1;
						}

						$x_pos = $pb['x_pos'];
						if (stristr($x_pos, '%')) {
							$x_pos = (float) $x_pos;
							$x_pos /= 100;
							$x_pos = ($pb['bpa']['w'] * $x_pos) - ($iw * $x_pos);
						}

						$y_pos = $pb['y_pos'];

						if (stristr($y_pos, '%')) {
							$y_pos = (float) $y_pos;
							$y_pos /= 100;
							$y_pos = ($pb['bpa']['h'] * $y_pos) - ($ih * $y_pos);
						}

						if ($nx > 1) {
							while ($x_pos > ($pb['x'] - $pb['bpa']['x'])) {
								$x_pos -= $iw;
							}
						}

						if ($ny > 1) {
							while ($y_pos > ($pb['y'] - $pb['bpa']['y'])) {
								$y_pos -= $ih;
							}
						}

						for ($xi = 0; $xi < $nx; $xi++) {
							for ($yi = 0; $yi < $ny; $yi++) {
								$x = $x0 + $x_pos + ($iw * $xi);
								$y = $y0 + $y_pos + ($ih * $yi);
								if ($pb['opacity'] > 0 && $pb['opacity'] < 1) {
									$opac = $this->SetAlpha($pb['opacity'], 'Normal', true);
								} else {
									$opac = '';
								}
								$s .= sprintf("q %s %.3F 0 0 %.3F %.3F %.3F cm /I%d Do Q", $opac, $iw * Mpdf::SCALE, $ih * Mpdf::SCALE, $x * Mpdf::SCALE, ($this->h - ($y + $ih)) * Mpdf::SCALE, $pb['image_id']) . "\n";
							}
						}

					} else {
						if (($pb['opacity'] > 0 || $pb['opacity'] === '0') && $pb['opacity'] < 1) {
							$opac = $this->SetAlpha($pb['opacity'], 'Normal', true);
						} else {
							$opac = '';
						}
						$s .= sprintf('q /Pattern cs /P%d scn %s %.3F %.3F %.3F %.3F re f Q', $n, $opac, $x, $y, $w, $h) . "\n";
					}

					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= 'Q' . "\n";
					}
				}

				if ((isset($pb['gradient']) && $pb['gradient']) || (isset($pb['image_id']) && $pb['image_id'])) {
					if ($pb['visibility'] != 'visible') {
						$s .= 'EMC' . "\n";
					}

					if ($pb['z-index'] > 0) {
						$s .= "\n" . 'EMCGZ-index' . "\n";
						$this->current_layer = 0;
					}
				}
			}
			/* -- END BACKGROUNDS -- */
		}

		return $s;
	}

	function PrintTableBackgrounds($adjustmenty = 0)
	{
		$s = '';
		/* -- BACKGROUNDS -- */
		ksort($this->tableBackgrounds);
		foreach ($this->tableBackgrounds as $bl => $pbs) {
			foreach ($pbs as $pb) {
				if ((!isset($pb['gradient']) || !$pb['gradient']) && (!isset($pb['image_id']) || !$pb['image_id'])) {
					$s .= 'q ' . $this->SetFColor($pb['col'], true) . "\n";
					if ($pb['col'][0] == 5) { // RGBa
						$s .= $this->SetAlpha(ord($pb['col'][4]) / 100, 'Normal', true, 'F') . "\n";
					} elseif ($pb['col'][0] == 6) { // CMYKa
						$s .= $this->SetAlpha(ord($pb['col'][5]) / 100, 'Normal', true, 'F') . "\n";
					}
					$s .= sprintf('%.3F %.3F %.3F %.3F re %s Q', $pb['x'] * Mpdf::SCALE, ($this->h - $pb['y']) * Mpdf::SCALE, $pb['w'] * Mpdf::SCALE, -$pb['h'] * Mpdf::SCALE, 'f') . "\n";
				}
				if (isset($pb['gradient']) && $pb['gradient']) {
					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= $pb['clippath'] . "\n";
					}
					$s .= $this->gradient->Gradient($pb['x'], $pb['y'], $pb['w'], $pb['h'], $pb['gradtype'], $pb['stops'], $pb['colorspace'], $pb['coords'], $pb['extend'], true);
					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= 'Q' . "\n";
					}
				}
				if (isset($pb['image_id']) && $pb['image_id']) { // Background pattern
					$pb['y'] -= $adjustmenty;
					$pb['h'] += $adjustmenty;
					$n = count($this->patterns) + 1;
					list($orig_w, $orig_h, $x_repeat, $y_repeat) = $this->_resizeBackgroundImage($pb['orig_w'], $pb['orig_h'], $pb['w'], $pb['h'], $pb['resize'], $pb['x_repeat'], $pb['y_repeat']);
					$this->patterns[$n] = ['x' => $pb['x'], 'y' => $pb['y'], 'w' => $pb['w'], 'h' => $pb['h'], 'pgh' => $this->h, 'image_id' => $pb['image_id'], 'orig_w' => $orig_w, 'orig_h' => $orig_h, 'x_pos' => $pb['x_pos'], 'y_pos' => $pb['y_pos'], 'x_repeat' => $x_repeat, 'y_repeat' => $y_repeat, 'itype' => $pb['itype']];
					$x = $pb['x'] * Mpdf::SCALE;
					$y = ($this->h - $pb['y']) * Mpdf::SCALE;
					$w = $pb['w'] * Mpdf::SCALE;
					$h = -$pb['h'] * Mpdf::SCALE;

					// mPDF 5.7.3
					if (($this->writingHTMLfooter || $this->writingHTMLheader) && (!isset($pb['clippath']) || $pb['clippath'] == '')) {
						// Set clipping path
						$pb['clippath'] = sprintf(' q 0 w %.3F %.3F m %.3F %.3F l %.3F %.3F l %.3F %.3F l %.3F %.3F l W n ', $x, $y, $x, $y + $h, $x + $w, $y + $h, $x + $w, $y, $x, $y);
					}

					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= $pb['clippath'] . "\n";
					}

					// mPDF 5.7.3
					if ($this->writingHTMLfooter || $this->writingHTMLheader) { // Write each (tiles) image rather than use as a pattern
						$iw = $pb['orig_w'] / Mpdf::SCALE;
						$ih = $pb['orig_h'] / Mpdf::SCALE;

						$w = $pb['w'];
						$h = $pb['h'];
						$x0 = $pb['x'];
						$y0 = $pb['y'];

						if (isset($pb['bpa']) && $pb['bpa']) {
							$w = $pb['bpa']['w'];
							$h = $pb['bpa']['h'];
							$x0 = $pb['bpa']['x'];
							$y0 = $pb['bpa']['y'];
						} // At present 'bpa' (background page area) is not set for tablebackgrounds - only pagebackgrounds
						// For now, just set it as:
						else {
							$pb['bpa'] = ['x' => $x0, 'y' => $y0, 'w' => $w, 'h' => $h];
						}

						if (isset($pb['size']['w']) && $pb['size']['w']) {
							$size = $pb['size'];

							if ($size['w'] == 'contain') {
								// Scale the image, while preserving its intrinsic aspect ratio (if any), to the largest size such that both its width and its height can fit inside the background positioning area.
								// Same as resize==3
								$ih = $ih * $pb['bpa']['w'] / $iw;
								$iw = $pb['bpa']['w'];
								if ($ih > $pb['bpa']['h']) {
									$iw = $iw * $pb['bpa']['h'] / $ih;
									$ih = $pb['bpa']['h'];
								}
							} elseif ($size['w'] == 'cover') {
								// Scale the image, while preserving its intrinsic aspect ratio (if any), to the smallest size such that both its width and its height can completely cover the background positioning area.
								$ih = $ih * $pb['bpa']['w'] / $iw;
								$iw = $pb['bpa']['w'];
								if ($ih < $pb['bpa']['h']) {
									$iw = $iw * $ih / $pb['bpa']['h'];
									$ih = $pb['bpa']['h'];
								}
							} else {
								if (NumericString::containsPercentChar($size['w'])) {
									$size['w'] = NumericString::removePercentChar($size['w']);
									$size['w'] /= 100;
									$size['w'] = ($pb['bpa']['w'] * $size['w']);
								}
								if (NumericString::containsPercentChar($size['h'])) {
									$size['h'] = NumericString::removePercentChar($size['h']);
									$size['h'] /= 100;
									$size['h'] = ($pb['bpa']['h'] * $size['h']);
								}
								if ($size['w'] == 'auto' && $size['h'] == 'auto') {
									$iw = $iw;
									$ih = $ih;
								} elseif ($size['w'] == 'auto' && $size['h'] != 'auto') {
									$iw = $iw * $size['h'] / $ih;
									$ih = $size['h'];
								} elseif ($size['w'] != 'auto' && $size['h'] == 'auto') {
									$ih = $ih * $size['w'] / $iw;
									$iw = $size['w'];
								} else {
									$iw = $size['w'];
									$ih = $size['h'];
								}
							}
						}

						// Number to repeat
						if (isset($pb['x_repeat']) && $pb['x_repeat']) {
							$nx = ceil($pb['w'] / $iw) + 1;
						} else {
							$nx = 1;
						}
						if (isset($pb['y_repeat']) && $pb['y_repeat']) {
							$ny = ceil($pb['h'] / $ih) + 1;
						} else {
							$ny = 1;
						}

						$x_pos = $pb['x_pos'];
						if (NumericString::containsPercentChar($x_pos)) {
							$x_pos = NumericString::removePercentChar($x_pos);
							$x_pos /= 100;
							$x_pos = ($pb['bpa']['w'] * $x_pos) - ($iw * $x_pos);
						}
						$y_pos = $pb['y_pos'];
						if (NumericString::containsPercentChar($y_pos)) {
							$y_pos = NumericString::removePercentChar($y_pos);
							$y_pos /= 100;
							$y_pos = ($pb['bpa']['h'] * $y_pos) - ($ih * $y_pos);
						}
						if ($nx > 1) {
							while ($x_pos > ($pb['x'] - $pb['bpa']['x'])) {
								$x_pos -= $iw;
							}
						}
						if ($ny > 1) {
							while ($y_pos > ($pb['y'] - $pb['bpa']['y'])) {
								$y_pos -= $ih;
							}
						}
						for ($xi = 0; $xi < $nx; $xi++) {
							for ($yi = 0; $yi < $ny; $yi++) {
								$x = $x0 + $x_pos + ($iw * $xi);
								$y = $y0 + $y_pos + ($ih * $yi);
								if ($pb['opacity'] > 0 && $pb['opacity'] < 1) {
									$opac = $this->SetAlpha($pb['opacity'], 'Normal', true);
								} else {
									$opac = '';
								}
								$s .= sprintf("q %s %.3F 0 0 %.3F %.3F %.3F cm /I%d Do Q", $opac, $iw * Mpdf::SCALE, $ih * Mpdf::SCALE, $x * Mpdf::SCALE, ($this->h - ($y + $ih)) * Mpdf::SCALE, $pb['image_id']) . "\n";
							}
						}
					} else {
						if (($pb['opacity'] > 0 || $pb['opacity'] === '0') && $pb['opacity'] < 1) {
							$opac = $this->SetAlpha($pb['opacity'], 'Normal', true);
						} else {
							$opac = '';
						}
						$s .= sprintf('q /Pattern cs /P%d scn %s %.3F %.3F %.3F %.3F re f Q', $n, $opac, $x, $y, $w, $h) . "\n";
					}

					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= 'Q' . "\n";
					}
				}
			}
		}
		/* -- END BACKGROUNDS -- */
		return $s;
	}

	function BeginLayer($id)
	{
		if ($this->current_layer > 0) {
			$this->EndLayer();
		}
		if ($id < 1) {
			return false;
		}
		if (!isset($this->layers[$id])) {
			$this->layers[$id] = ['name' => 'Layer ' . ($id)];
			if (($this->PDFA || $this->PDFX)) {
				$this->PDFAXwarnings[] = "Cannot use layers when using PDFA or PDFX";
				return '';
			} elseif (!$this->PDFA && !$this->PDFX) {
				$this->pdf_version = '1.5';
			}
		}
		$this->current_layer = $id;
		$this->writer->write('/OCZ-index /ZI' . $id . ' BDC');

		$this->pageoutput[$this->page] = [];
	}

	function EndLayer()
	{
		if ($this->current_layer > 0) {
			$this->writer->write('EMCZ-index');
			$this->current_layer = 0;
		}
	}

	function AddPageByArray($a)
	{
		if (!is_array($a)) {
			$a = [];
		}

		$orientation = (isset($a['orientation']) ? $a['orientation'] : '');
		$condition = (isset($a['condition']) ? $a['condition'] : (isset($a['type']) ? $a['type'] : ''));
		$resetpagenum = (isset($a['resetpagenum']) ? $a['resetpagenum'] : '');
		$pagenumstyle = (isset($a['pagenumstyle']) ? $a['pagenumstyle'] : '');
		$suppress = (isset($a['suppress']) ? $a['suppress'] : '');
		$mgl = (isset($a['mgl']) ? $a['mgl'] : (isset($a['margin-left']) ? $a['margin-left'] : ''));
		$mgr = (isset($a['mgr']) ? $a['mgr'] : (isset($a['margin-right']) ? $a['margin-right'] : ''));
		$mgt = (isset($a['mgt']) ? $a['mgt'] : (isset($a['margin-top']) ? $a['margin-top'] : ''));
		$mgb = (isset($a['mgb']) ? $a['mgb'] : (isset($a['margin-bottom']) ? $a['margin-bottom'] : ''));
		$mgh = (isset($a['mgh']) ? $a['mgh'] : (isset($a['margin-header']) ? $a['margin-header'] : ''));
		$mgf = (isset($a['mgf']) ? $a['mgf'] : (isset($a['margin-footer']) ? $a['margin-footer'] : ''));
		$ohname = (isset($a['ohname']) ? $a['ohname'] : (isset($a['odd-header-name']) ? $a['odd-header-name'] : ''));
		$ehname = (isset($a['ehname']) ? $a['ehname'] : (isset($a['even-header-name']) ? $a['even-header-name'] : ''));
		$ofname = (isset($a['ofname']) ? $a['ofname'] : (isset($a['odd-footer-name']) ? $a['odd-footer-name'] : ''));
		$efname = (isset($a['efname']) ? $a['efname'] : (isset($a['even-footer-name']) ? $a['even-footer-name'] : ''));
		$ohvalue = (isset($a['ohvalue']) ? $a['ohvalue'] : (isset($a['odd-header-value']) ? $a['odd-header-value'] : 0));
		$ehvalue = (isset($a['ehvalue']) ? $a['ehvalue'] : (isset($a['even-header-value']) ? $a['even-header-value'] : 0));
		$ofvalue = (isset($a['ofvalue']) ? $a['ofvalue'] : (isset($a['odd-footer-value']) ? $a['odd-footer-value'] : 0));
		$efvalue = (isset($a['efvalue']) ? $a['efvalue'] : (isset($a['even-footer-value']) ? $a['even-footer-value'] : 0));
		$pagesel = (isset($a['pagesel']) ? $a['pagesel'] : (isset($a['pageselector']) ? $a['pageselector'] : ''));
		$newformat = (isset($a['newformat']) ? $a['newformat'] : (isset($a['sheet-size']) ? $a['sheet-size'] : ''));

		$this->AddPage($orientation, $condition, $resetpagenum, $pagenumstyle, $suppress, $mgl, $mgr, $mgt, $mgb, $mgh, $mgf, $ohname, $ehname, $ofname, $efname, $ohvalue, $ehvalue, $ofvalue, $efvalue, $pagesel, $newformat);
	}

	// mPDF 6 pagebreaktype
	function _preForcedPagebreak($pagebreaktype)
	{
		if ($pagebreaktype == 'cloneall') {
			// Close any open block tags
			$arr = [];
			$ai = 0;
			for ($b = $this->blklvl; $b > 0; $b--) {
				$this->tag->CloseTag($this->blk[$b]['tag'], $arr, $ai);
			}
			if ($this->blklvl == 0 && !empty($this->textbuffer)) { // Output previously buffered content
				$this->printbuffer($this->textbuffer, 1);
				$this->textbuffer = [];
			}
		} elseif ($pagebreaktype == 'clonebycss') {
			// Close open block tags whilst box-decoration-break==clone
			$arr = [];
			$ai = 0;
			for ($b = $this->blklvl; $b > 0; $b--) {
				if (isset($this->blk[$b]['box_decoration_break']) && $this->blk[$b]['box_decoration_break'] == 'clone') {
					$this->tag->CloseTag($this->blk[$b]['tag'], $arr, $ai);
				} else {
					if ($b == $this->blklvl && !empty($this->textbuffer)) { // Output previously buffered content
						$this->printbuffer($this->textbuffer, 1);
						$this->textbuffer = [];
					}
					break;
				}
			}
		} elseif (!empty($this->textbuffer)) { // Output previously buffered content
			$this->printbuffer($this->textbuffer, 1);
			$this->textbuffer = [];
		}
	}

	// mPDF 6 pagebreaktype
	function _postForcedPagebreak($pagebreaktype, $startpage, $save_blk, $save_blklvl)
	{
		if ($pagebreaktype == 'cloneall') {
			$this->blk = [];
			$this->blk[0] = $save_blk[0];
			// Re-open block tags
			$this->blklvl = 0;
			$arr = [];
			$i = 0;
			for ($b = 1; $b <= $save_blklvl; $b++) {
				$this->tag->OpenTag($save_blk[$b]['tag'], $save_blk[$b]['attr'], $arr, $i);
			}
		} elseif ($pagebreaktype == 'clonebycss') {
			$this->blk = [];
			$this->blk[0] = $save_blk[0];
			// Don't re-open tags for lowest level elements - so need to do some adjustments
			for ($b = 1; $b <= $this->blklvl; $b++) {
				$this->blk[$b] = $save_blk[$b];
				$this->blk[$b]['startpage'] = 0;
				$this->blk[$b]['y0'] = $this->y; // ?? $this->tMargin
				if (($this->page - $startpage) % 2) {
					if (isset($this->blk[$b]['x0'])) {
						$this->blk[$b]['x0'] += $this->MarginCorrection;
					} else {
						$this->blk[$b]['x0'] = $this->MarginCorrection;
					}
				}
				// for Float DIV
				$this->blk[$b]['marginCorrected'][$this->page] = true;
			}

			// Re-open block tags for any that have box_decoration_break==clone
			$arr = [];
			$i = 0;
			for ($b = $this->blklvl + 1; $b <= $save_blklvl; $b++) {
				if ($b < $this->blklvl) {
					$this->lastblocklevelchange = -1;
				}
				$this->tag->OpenTag($save_blk[$b]['tag'], $save_blk[$b]['attr'], $arr, $i);
			}
			if ($this->blk[$this->blklvl]['box_decoration_break'] != 'clone') {
				$this->lastblocklevelchange = -1;
			}
		} else {
			$this->lastblocklevelchange = -1;
		}
	}

	function AddPage(
		$orientation = '',
		$condition = '',
		$resetpagenum = '',
		$pagenumstyle = '',
		$suppress = '',
		$mgl = '',
		$mgr = '',
		$mgt = '',
		$mgb = '',
		$mgh = '',
		$mgf = '',
		$ohname = '',
		$ehname = '',
		$ofname = '',
		$efname = '',
		$ohvalue = 0,
		$ehvalue = 0,
		$ofvalue = 0,
		$efvalue = 0,
		$pagesel = '',
		$newformat = ''
	) {
		/* -- CSS-FLOAT -- */
		// Float DIV
		// Cannot do with columns on, or if any change in page orientation/margins etc.
		// If next page already exists - i.e background /headers and footers already written
		if ($this->state > 0 && $this->page < count($this->pages)) {
			$bak_cml = $this->cMarginL;
			$bak_cmr = $this->cMarginR;
			$bak_dw = $this->divwidth;
			// Paint Div Border if necessary
			if ($this->blklvl > 0) {
				$save_tr = $this->table_rotate; // *TABLES*
				$this->table_rotate = 0; // *TABLES*
				if (isset($this->blk[$this->blklvl]['y0']) && $this->y == $this->blk[$this->blklvl]['y0']) {
					$this->blk[$this->blklvl]['startpage'] ++;
				}
				if ((isset($this->blk[$this->blklvl]['y0']) && $this->y > $this->blk[$this->blklvl]['y0']) || $this->flowingBlockAttr['is_table']) {
					$toplvl = $this->blklvl;
				} else {
					$toplvl = $this->blklvl - 1;
				}
				$sy = $this->y;
				for ($bl = 1; $bl <= $toplvl; $bl++) {
					$this->PaintDivBB('pagebottom', 0, $bl);
				}
				$this->y = $sy;
				$this->table_rotate = $save_tr; // *TABLES*
			}
			$s = $this->PrintPageBackgrounds();

			// Writes after the marker so not overwritten later by page background etc.
			$this->pages[$this->page] = preg_replace(
				'/(___BACKGROUND___PATTERNS' . $this->uniqstr . ')/',
				'\\1' . "\n" . $s . "\n",
				$this->pages[$this->page]
			);

			$this->pageBackgrounds = [];
			$family = $this->FontFamily;
			$style = $this->FontStyle;
			$size = $this->FontSizePt;
			$lw = $this->LineWidth;
			$dc = $this->DrawColor;
			$fc = $this->FillColor;
			$tc = $this->TextColor;
			$cf = $this->ColorFlag;

			$this->printfloatbuffer();

			// Move to next page
			$this->page++;

			$this->ResetMargins();
			$this->SetAutoPageBreak($this->autoPageBreak, $this->bMargin);
			$this->x = $this->lMargin;
			$this->y = $this->tMargin;
			$this->FontFamily = '';
			$this->writer->write('2 J');
			$this->LineWidth = $lw;
			$this->writer->write(sprintf('%.3F w', $lw * Mpdf::SCALE));

			if ($family) {
				$this->SetFont($family, $style, $size, true, true);
			}

			$this->DrawColor = $dc;

			if ($dc != $this->defDrawColor) {
				$this->writer->write($dc);
			}

			$this->FillColor = $fc;

			if ($fc != $this->defFillColor) {
				$this->writer->write($fc);
			}

			$this->TextColor = $tc;
			$this->ColorFlag = $cf;

			for ($bl = 1; $bl <= $this->blklvl; $bl++) {
				$this->blk[$bl]['y0'] = $this->y;
				// Don't correct more than once for background DIV containing a Float
				if (!isset($this->blk[$bl]['marginCorrected'][$this->page])) {
					if (isset($this->blk[$bl]['x0'])) {
						$this->blk[$bl]['x0'] += $this->MarginCorrection;
					} else {
						$this->blk[$bl]['x0'] = $this->MarginCorrection;
					}
				}
				$this->blk[$bl]['marginCorrected'][$this->page] = true;
			}

			$this->cMarginL = $bak_cml;
			$this->cMarginR = $bak_cmr;
			$this->divwidth = $bak_dw;

			return '';
		}
		/* -- END CSS-FLOAT -- */

		// Start a new page
		if ($this->state == 0) {
			$this->Open();
		}

		$bak_cml = $this->cMarginL;
		$bak_cmr = $this->cMarginR;
		$bak_dw = $this->divwidth;

		$bak_lh = $this->lineheight;

		$orientation = substr(strtoupper($orientation), 0, 1);
		$condition = strtoupper($condition);


		if ($condition == 'E') { // only adds new page if needed to create an Even page
			if (!$this->mirrorMargins || ($this->page) % 2 == 0) {
				return false;
			}
		} elseif ($condition == 'O') { // only adds new page if needed to create an Odd page
			if (!$this->mirrorMargins || ($this->page) % 2 == 1) {
				return false;
			}
		} elseif ($condition == 'NEXT-EVEN') { // always adds at least one new page to create an Even page
			if (!$this->mirrorMargins) {
				$condition = '';
			} else {
				if ($pagesel) {
					$pbch = $pagesel;
					$pagesel = '';
				} // *CSS-PAGE*
				else {
					$pbch = false;
				} // *CSS-PAGE*
				$this->AddPage($this->CurOrientation, 'O');
				$this->extrapagebreak = true; // mPDF 6 pagebreaktype
				if ($pbch) {
					$pagesel = $pbch;
				} // *CSS-PAGE*
				$condition = '';
			}
		} elseif ($condition == 'NEXT-ODD') { // always adds at least one new page to create an Odd page
			if (!$this->mirrorMargins) {
				$condition = '';
			} else {
				if ($pagesel) {
					$pbch = $pagesel;
					$pagesel = '';
				} // *CSS-PAGE*
				else {
					$pbch = false;
				} // *CSS-PAGE*
				$this->AddPage($this->CurOrientation, 'E');
				$this->extrapagebreak = true; // mPDF 6 pagebreaktype
				if ($pbch) {
					$pagesel = $pbch;
				} // *CSS-PAGE*
				$condition = '';
			}
		}

		if ($resetpagenum || $pagenumstyle || $suppress) {
			$this->PageNumSubstitutions[] = ['from' => ($this->page + 1), 'reset' => $resetpagenum, 'type' => $pagenumstyle, 'suppress' => $suppress];
		}

		$save_tr = $this->table_rotate; // *TABLES*
		$this->table_rotate = 0; // *TABLES*
		$save_kwt = $this->kwt;
		$this->kwt = 0;
		$save_layer = $this->current_layer;
		$save_vis = $this->visibility;

		if ($this->visibility != 'visible') {
			$this->SetVisibility('visible');
		}

		$this->EndLayer();

		// Paint Div Border if necessary
		// PAINTS BACKGROUND COLOUR OR BORDERS for DIV - DISABLED FOR COLUMNS (cf. AcceptPageBreak) AT PRESENT in ->PaintDivBB
		if (!$this->ColActive && $this->blklvl > 0) {
			if (isset($this->blk[$this->blklvl]['y0']) && $this->y == $this->blk[$this->blklvl]['y0'] && !$this->extrapagebreak) { // mPDF 6 pagebreaktype
				if (isset($this->blk[$this->blklvl]['startpage'])) {
					$this->blk[$this->blklvl]['startpage'] ++;
				} else {
					$this->blk[$this->blklvl]['startpage'] = 1;
				}
			}
			if ((isset($this->blk[$this->blklvl]['y0']) && $this->y > $this->blk[$this->blklvl]['y0']) || $this->flowingBlockAttr['is_table'] || $this->extrapagebreak) {
				$toplvl = $this->blklvl;
			} // mPDF 6 pagebreaktype
			else {
				$toplvl = $this->blklvl - 1;
			}
			$sy = $this->y;
			for ($bl = 1; $bl <= $toplvl; $bl++) {
				if (isset($this->blk[$bl]['z-index']) && $this->blk[$bl]['z-index'] > 0) {
					$this->BeginLayer($this->blk[$bl]['z-index']);
				}
				if (isset($this->blk[$bl]['visibility']) && $this->blk[$bl]['visibility'] && $this->blk[$bl]['visibility'] != 'visible') {
					$this->SetVisibility($this->blk[$bl]['visibility']);
				}
				$this->PaintDivBB('pagebottom', 0, $bl);
			}
			$this->y = $sy;
			// RESET block y0 and x0 - see below
		}
		$this->extrapagebreak = false; // mPDF 6 pagebreaktype

		if ($this->visibility != 'visible') {
			$this->SetVisibility('visible');
		}

		$this->EndLayer();

		// BODY Backgrounds
		if ($this->page > 0) {
			$s = '';
			$s .= $this->PrintBodyBackgrounds();

			$s .= $this->PrintPageBackgrounds();
			$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS' . $this->uniqstr . ')/', "\n" . $s . "\n" . '\\1', $this->pages[$this->page]);
			$this->pageBackgrounds = [];
		}

		$save_kt = $this->keep_block_together;
		$this->keep_block_together = 0;

		$save_cols = false;

		/* -- COLUMNS -- */
		if ($this->ColActive) {
			$save_cols = true;
			$save_nbcol = $this->NbCol; // other values of gap and vAlign will not change by setting Columns off
			$this->SetColumns(0);
		}
		/* -- END COLUMNS -- */

		$family = $this->FontFamily;
		$style = $this->FontStyle;
		$size = $this->FontSizePt;
		$this->ColumnAdjust = true; // enables column height adjustment for the page
		$lw = $this->LineWidth;
		$dc = $this->DrawColor;
		$fc = $this->FillColor;
		$tc = $this->TextColor;
		$cf = $this->ColorFlag;
		if ($this->page > 0) {
			// Page footer
			$this->InFooter = true;

			$this->Reset();
			$this->pageoutput[$this->page] = [];

			$this->Footer();
			// Close page
			$this->_endpage();
		}

		// Start new page
		$this->_beginpage($orientation, $mgl, $mgr, $mgt, $mgb, $mgh, $mgf, $ohname, $ehname, $ofname, $efname, $ohvalue, $ehvalue, $ofvalue, $efvalue, $pagesel, $newformat);

		if ($this->docTemplate) {
			$currentReaderId = $this->currentReaderId;

			$pagecount = $this->setSourceFile($this->docTemplate);
			if (($this->page - $this->docTemplateStart) > $pagecount) {
				if ($this->docTemplateContinue) {
					$tplIdx = $this->importPage($pagecount);
					$this->useTemplate($tplIdx);
				}
			} else {
				$tplIdx = $this->importPage(($this->page - $this->docTemplateStart));
				$this->useTemplate($tplIdx);
			}

			$this->currentReaderId = $currentReaderId;
		}

		if ($this->pageTemplate) {
			$this->useTemplate($this->pageTemplate);
		}

		// Tiling Patterns
		$this->writer->write('___PAGE___START' . $this->uniqstr);
		$this->writer->write('___BACKGROUND___PATTERNS' . $this->uniqstr);
		$this->writer->write('___HEADER___MARKER' . $this->uniqstr);
		$this->pageBackgrounds = [];

		// Set line cap style to square
		$this->SetLineCap(2);
		// Set line width
		$this->LineWidth = $lw;
		$this->writer->write(sprintf('%.3F w', $lw * Mpdf::SCALE));
		// Set font
		if ($family) {
			$this->SetFont($family, $style, $size, true, true); // forces write
		}

		// Set colors
		$this->DrawColor = $dc;
		if ($dc != $this->defDrawColor) {
			$this->writer->write($dc);
		}
		$this->FillColor = $fc;
		if ($fc != $this->defFillColor) {
			$this->writer->write($fc);
		}
		$this->TextColor = $tc;
		$this->ColorFlag = $cf;

		// Page header
		$this->Header();

		// Restore line width
		if ($this->LineWidth != $lw) {
			$this->LineWidth = $lw;
			$this->writer->write(sprintf('%.3F w', $lw * Mpdf::SCALE));
		}
		// Restore font
		if ($family) {
			$this->SetFont($family, $style, $size, true, true); // forces write
		}

		// Restore colors
		if ($this->DrawColor != $dc) {
			$this->DrawColor = $dc;
			$this->writer->write($dc);
		}
		if ($this->FillColor != $fc) {
			$this->FillColor = $fc;
			$this->writer->write($fc);
		}
		$this->TextColor = $tc;
		$this->ColorFlag = $cf;
		$this->InFooter = false;

		if ($save_layer > 0) {
			$this->BeginLayer($save_layer);
		}

		if ($save_vis != 'visible') {
			$this->SetVisibility($save_vis);
		}

		/* -- COLUMNS -- */
		if ($save_cols) {
			// Restore columns
			$this->SetColumns($save_nbcol, $this->colvAlign, $this->ColGap);
		}
		if ($this->ColActive) {
			$this->SetCol(0);
		}
		/* -- END COLUMNS -- */


		// RESET BLOCK BORDER TOP
		if (!$this->ColActive) {
			for ($bl = 1; $bl <= $this->blklvl; $bl++) {
				$this->blk[$bl]['y0'] = $this->y;
				if (isset($this->blk[$bl]['x0'])) {
					$this->blk[$bl]['x0'] += $this->MarginCorrection;
				} else {
					$this->blk[$bl]['x0'] = $this->MarginCorrection;
				}
				// Added mPDF 3.0 Float DIV
				$this->blk[$bl]['marginCorrected'][$this->page] = true;
			}
		}


		$this->table_rotate = $save_tr; // *TABLES*
		$this->kwt = $save_kwt;

		$this->keep_block_together = $save_kt;

		$this->cMarginL = $bak_cml;
		$this->cMarginR = $bak_cmr;
		$this->divwidth = $bak_dw;

		$this->lineheight = $bak_lh;
	}

	/**
	 * Get current page number
	 *
	 * @return int
	 */
	function PageNo()
	{
		return $this->page;
	}

	function AddSpotColorsFromFile($file)
	{
		$colors = @file($file);
		if (!$colors) {
			throw new \Mpdf\MpdfException("Cannot load spot colors file - " . $file);
		}
		foreach ($colors as $sc) {
			list($name, $c, $m, $y, $k) = preg_split("/\t/", $sc);
			$c = intval($c);
			$m = intval($m);
			$y = intval($y);
			$k = intval($k);
			$this->AddSpotColor($name, $c, $m, $y, $k);
		}
	}

	function AddSpotColor($name, $c, $m, $y, $k)
	{
		$name = strtoupper(trim($name));
		if (!isset($this->spotColors[$name])) {
			$i = count($this->spotColors) + 1;
			$this->spotColors[$name] = ['i' => $i, 'c' => $c, 'm' => $m, 'y' => $y, 'k' => $k];
			$this->spotColorIDs[$i] = $name;
		}
	}

	function SetColor($col, $type = '')
	{
		$out = '';
		if (!$col) {
			return '';
		} // mPDF 6
		if ($col[0] == 3 || $col[0] == 5) { // RGB / RGBa
			$out = sprintf('%.3F %.3F %.3F rg', ord($col[1]) / 255, ord($col[2]) / 255, ord($col[3]) / 255);
		} elseif ($col[0] == 1) { // GRAYSCALE
			$out = sprintf('%.3F g', ord($col[1]) / 255);
		} elseif ($col[0] == 2) { // SPOT COLOR
			$out = sprintf('/CS%d cs %.3F scn', ord($col[1]), ord($col[2]) / 100);
		} elseif ($col[0] == 4 || $col[0] == 6) { // CMYK / CMYKa
			$out = sprintf('%.3F %.3F %.3F %.3F k', ord($col[1]) / 100, ord($col[2]) / 100, ord($col[3]) / 100, ord($col[4]) / 100);
		}
		if ($type == 'Draw') {
			$out = strtoupper($out);
		} // e.g. rg => RG
		elseif ($type == 'CodeOnly') {
			$out = preg_replace('/\s(rg|g|k)/', '', $out);
		}
		return $out;
	}

	function SetDColor($col, $return = false)
	{
		$out = $this->SetColor($col, 'Draw');
		if ($return) {
			return $out;
		}
		if ($out == '') {
			return '';
		}
		$this->DrawColor = $out;
		if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['DrawColor']) && $this->pageoutput[$this->page]['DrawColor'] != $this->DrawColor) || !isset($this->pageoutput[$this->page]['DrawColor']))) {
			$this->writer->write($this->DrawColor);
		}
		$this->pageoutput[$this->page]['DrawColor'] = $this->DrawColor;
	}

	function SetFColor($col, $return = false)
	{
		$out = $this->SetColor($col, 'Fill');
		if ($return) {
			return $out;
		}
		if ($out == '') {
			return '';
		}
		$this->FillColor = $out;
		$this->ColorFlag = ($out != $this->TextColor);
		if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['FillColor']) && $this->pageoutput[$this->page]['FillColor'] != $this->FillColor) || !isset($this->pageoutput[$this->page]['FillColor']))) {
			$this->writer->write($this->FillColor);
		}
		$this->pageoutput[$this->page]['FillColor'] = $this->FillColor;
	}

	function SetTColor($col, $return = false)
	{
		$out = $this->SetColor($col, 'Text');
		if ($return) {
			return $out;
		}
		if ($out == '') {
			return '';
		}
		$this->TextColor = $out;
		$this->ColorFlag = ($this->FillColor != $out);
	}

	function SetDrawColor($r, $g = -1, $b = -1, $col4 = -1, $return = false)
	{
		// Set color for all stroking operations
		$col = [];
		if (($r == 0 and $g == 0 and $b == 0 && $col4 == -1) or $g == -1) {
			$col = $this->colorConverter->convert($r, $this->PDFAXwarnings);
		} elseif ($col4 == -1) {
			$col = $this->colorConverter->convert('rgb(' . $r . ',' . $g . ',' . $b . ')', $this->PDFAXwarnings);
		} else {
			$col = $this->colorConverter->convert('cmyk(' . $r . ',' . $g . ',' . $b . ',' . $col4 . ')', $this->PDFAXwarnings);
		}
		$out = $this->SetDColor($col, $return);
		return $out;
	}

	function SetFillColor($r, $g = -1, $b = -1, $col4 = -1, $return = false)
	{
		// Set color for all filling operations
		$col = [];
		if (($r == 0 and $g == 0 and $b == 0 && $col4 == -1) or $g == -1) {
			$col = $this->colorConverter->convert($r, $this->PDFAXwarnings);
		} elseif ($col4 == -1) {
			$col = $this->colorConverter->convert('rgb(' . $r . ',' . $g . ',' . $b . ')', $this->PDFAXwarnings);
		} else {
			$col = $this->colorConverter->convert('cmyk(' . $r . ',' . $g . ',' . $b . ',' . $col4 . ')', $this->PDFAXwarnings);
		}
		$out = $this->SetFColor($col, $return);
		return $out;
	}

	function SetTextColor($r, $g = -1, $b = -1, $col4 = -1, $return = false)
	{
		// Set color for text
		$col = [];
		if (($r == 0 and $g == 0 and $b == 0 && $col4 == -1) or $g == -1) {
			$col = $this->colorConverter->convert($r, $this->PDFAXwarnings);
		} elseif ($col4 == -1) {
			$col = $this->colorConverter->convert('rgb(' . $r . ',' . $g . ',' . $b . ')', $this->PDFAXwarnings);
		} else {
			$col = $this->colorConverter->convert('cmyk(' . $r . ',' . $g . ',' . $b . ',' . $col4 . ')', $this->PDFAXwarnings);
		}
		$out = $this->SetTColor($col, $return);
		return $out;
	}

	function _getCharWidth(&$cw, $u, $isdef = true)
	{
		$w = 0;

		if ($u == 0) {
			$w = false;
		} elseif (isset($cw[$u * 2 + 1])) {
			$w = (ord($cw[$u * 2]) << 8) + ord($cw[$u * 2 + 1]);
		}

		if ($w == 65535) {
			return 0;
		} elseif ($w) {
			return $w;
		} elseif ($isdef) {
			return false;
		} else {
			return 0;
		}
	}

	function _charDefined(&$cw, $u)
	{
		$w = 0;
		if ($u == 0) {
			return false;
		}
		if (isset($cw[$u * 2 + 1])) {
			$w = (ord($cw[$u * 2]) << 8) + ord($cw[$u * 2 + 1]);
		}

		return (bool) $w;
	}

	function GetCharWidthCore($c)
	{
		// Get width of a single character in the current Core font
		$c = (string) $c;
		$w = 0;
		// Soft Hyphens chr(173)
		if ($c == chr(173) && $this->FontFamily != 'csymbol' && $this->FontFamily != 'czapfdingbats') {
			return 0;
		} elseif (($this->textvar & TextVars::FC_SMALLCAPS) && isset($this->upperCase[ord($c)])) {  // mPDF 5.7.1
			$charw = $this->CurrentFont['cw'][chr($this->upperCase[ord($c)])];
			if ($charw !== false) {
				$charw = $charw * $this->smCapsScale * $this->smCapsStretch / 100;
				$w+=$charw;
			}
		} elseif (isset($this->CurrentFont['cw'][$c])) {
			$w += $this->CurrentFont['cw'][$c];
		} elseif (isset($this->CurrentFont['cw'][ord($c)])) {
			$w += $this->CurrentFont['cw'][ord($c)];
		}
		$w *= ($this->FontSize / 1000);
		if ($this->minwSpacing || $this->fixedlSpacing) {
			if ($c == ' ') {
				$nb_spaces = 1;
			} else {
				$nb_spaces = 0;
			}
			$w += $this->fixedlSpacing + ($nb_spaces * $this->minwSpacing);
		}
		return ($w);
	}

	function GetCharWidthNonCore($c, $addSubset = true)
	{
		// Get width of a single character in the current Non-Core font
		$c = (string) $c;
		$w = 0;
		$unicode = $this->UTF8StringToArray($c, $addSubset);
		$char = $unicode[0];
		/* -- CJK-FONTS -- */
		if ($this->CurrentFont['type'] == 'Type0') { // CJK Adobe fonts
			if ($char == 173) {
				return 0;
			} // Soft Hyphens
			elseif (isset($this->CurrentFont['cw'][$char])) {
				$w+=$this->CurrentFont['cw'][$char];
			} elseif (isset($this->CurrentFont['MissingWidth'])) {
				$w += $this->CurrentFont['MissingWidth'];
			} else {
				$w += 500;
			}
		} else {
			/* -- END CJK-FONTS -- */
			if ($char == 173) {
				return 0;
			} // Soft Hyphens
			elseif (($this->textvar & TextVars::FC_SMALLCAPS) && isset($this->upperCase[$char])) { // mPDF 5.7.1
				$charw = $this->_getCharWidth($this->CurrentFont['cw'], $this->upperCase[$char]);
				if ($charw !== false) {
					$charw = $charw * $this->smCapsScale * $this->smCapsStretch / 100;
					$w+=$charw;
				} elseif (isset($this->CurrentFont['desc']['MissingWidth'])) {
					$w += $this->CurrentFont['desc']['MissingWidth'];
				} elseif (isset($this->CurrentFont['MissingWidth'])) {
					$w += $this->CurrentFont['MissingWidth'];
				} else {
					$w += 500;
				}
			} else {
				$charw = $this->_getCharWidth($this->CurrentFont['cw'], $char);
				if ($charw !== false) {
					$w+=$charw;
				} elseif (isset($this->CurrentFont['desc']['MissingWidth'])) {
					$w += $this->CurrentFont['desc']['MissingWidth'];
				} elseif (isset($this->CurrentFont['MissingWidth'])) {
					$w += $this->CurrentFont['MissingWidth'];
				} else {
					$w += 500;
				}
			}
		} // *CJK-FONTS*
		$w *= ($this->FontSize / 1000);
		if ($this->minwSpacing || $this->fixedlSpacing) {
			if ($c == ' ') {
				$nb_spaces = 1;
			} else {
				$nb_spaces = 0;
			}
			$w += $this->fixedlSpacing + ($nb_spaces * $this->minwSpacing);
		}
		return ($w);
	}

	function GetCharWidth($c, $addSubset = true)
	{
		if (!$this->usingCoreFont) {
			return $this->GetCharWidthNonCore($c, $addSubset);
		} else {
			return $this->GetCharWidthCore($c);
		}
	}

	function GetStringWidth($s, $addSubset = true, $OTLdata = false, $textvar = 0, $includeKashida = false)
	{
	// mPDF 5.7.1
		// Get width of a string in the current font
		$s = (string) $s;
		$cw = &$this->CurrentFont['cw'];
		$w = 0;
		$kerning = 0;
		$lastchar = 0;
		$nb_carac = 0;
		$nb_spaces = 0;
		$kashida = 0;
		// mPDF ITERATION
		if ($this->iterationCounter) {
			$s = preg_replace('/{iteration ([a-zA-Z0-9_]+)}/', '\\1', $s);
		}
		if (!$this->usingCoreFont) {
			$discards = substr_count($s, "\xc2\xad"); // mPDF 6 soft hyphens [U+00AD]
			$unicode = $this->UTF8StringToArray($s, $addSubset);
			if ($this->minwSpacing || $this->fixedlSpacing) {
				$nb_spaces = mb_substr_count($s, ' ', $this->mb_enc);
				$nb_carac = count($unicode) - $discards; // mPDF 6
				// mPDF 5.7.1
				// Use GPOS OTL
				if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
					if (isset($OTLdata['group']) && $OTLdata['group']) {
						$nb_carac -= substr_count($OTLdata['group'], 'M');
					}
				}
			}
			/* -- CJK-FONTS -- */
			if ($this->CurrentFont['type'] == 'Type0') { // CJK Adobe fonts
				foreach ($unicode as $char) {
					if ($char == 0x00AD) {
						continue;
					} // mPDF 6 soft hyphens [U+00AD]
					if (isset($cw[$char])) {
						$w+=$cw[$char];
					} elseif (isset($this->CurrentFont['MissingWidth'])) {
						$w += $this->CurrentFont['MissingWidth'];
					} else {
						$w += 500;
					}
				}
			} else {
				/* -- END CJK-FONTS -- */
				foreach ($unicode as $i => $char) {
					if ($char == 0x00AD) {
						continue;
					} // mPDF 6 soft hyphens [U+00AD]
					if (($textvar & TextVars::FC_SMALLCAPS) && isset($this->upperCase[$char])) {
						$charw = $this->_getCharWidth($cw, $this->upperCase[$char]);
						if ($charw !== false) {
							$charw = $charw * $this->smCapsScale * $this->smCapsStretch / 100;
							$w+=$charw;
						} elseif (isset($this->CurrentFont['desc']['MissingWidth'])) {
							$w += $this->CurrentFont['desc']['MissingWidth'];
						} elseif (isset($this->CurrentFont['MissingWidth'])) {
							$w += $this->CurrentFont['MissingWidth'];
						} else {
							$w += 500;
						}
					} else {
						$charw = $this->_getCharWidth($cw, $char);
						if ($charw !== false) {
							$w+=$charw;
						} elseif (isset($this->CurrentFont['desc']['MissingWidth'])) {
							$w += $this->CurrentFont['desc']['MissingWidth'];
						} elseif (isset($this->CurrentFont['MissingWidth'])) {
							$w += $this->CurrentFont['MissingWidth'];
						} else {
							$w += 500;
						}
						// mPDF 5.7.1
						// Use GPOS OTL
						// ...GetStringWidth...
						if (isset($this->CurrentFont['useOTL']) && ($this->CurrentFont['useOTL'] & 0xFF) && !empty($OTLdata)) {
							if (isset($OTLdata['GPOSinfo'][$i]['wDir']) && $OTLdata['GPOSinfo'][$i]['wDir'] == 'RTL') {
								if (isset($OTLdata['GPOSinfo'][$i]['XAdvanceR']) && $OTLdata['GPOSinfo'][$i]['XAdvanceR']) {
									$w += $OTLdata['GPOSinfo'][$i]['XAdvanceR'] * 1000 / $this->CurrentFont['unitsPerEm'];
								}
							} else {
								if (isset($OTLdata['GPOSinfo'][$i]['XAdvanceL']) && $OTLdata['GPOSinfo'][$i]['XAdvanceL']) {
									$w += $OTLdata['GPOSinfo'][$i]['XAdvanceL'] * 1000 / $this->CurrentFont['unitsPerEm'];
								}
							}
							// Kashida from GPOS
							// Kashida is set as an absolute length value (already set as a proportion based on useKashida %)
							if ($includeKashida && isset($OTLdata['GPOSinfo'][$i]['kashida_space']) && $OTLdata['GPOSinfo'][$i]['kashida_space']) {
								$kashida += $OTLdata['GPOSinfo'][$i]['kashida_space'];
							}
						}
						if (($textvar & TextVars::FC_KERNING) && $lastchar) {
							if (isset($this->CurrentFont['kerninfo'][$lastchar][$char])) {
								$kerning += $this->CurrentFont['kerninfo'][$lastchar][$char];
							}
						}
						$lastchar = $char;
					}
				}
			} // *CJK-FONTS*
		} else {
			if ($this->FontFamily != 'csymbol' && $this->FontFamily != 'czapfdingbats') {
				$s = str_replace(chr(173), '', $s);
			}
			$nb_carac = $l = strlen($s);
			if ($this->minwSpacing || $this->fixedlSpacing) {
				$nb_spaces = substr_count($s, ' ');
			}
			for ($i = 0; $i < $l; $i++) {
				if (($textvar & TextVars::FC_SMALLCAPS) && isset($this->upperCase[ord($s[$i])])) {  // mPDF 5.7.1
					$charw = $cw[chr($this->upperCase[ord($s[$i])])];
					if ($charw !== false) {
						$charw = $charw * $this->smCapsScale * $this->smCapsStretch / 100;
						$w+=$charw;
					}
				} elseif (isset($cw[$s[$i]])) {
					$w += $cw[$s[$i]];
				} elseif (isset($cw[ord($s[$i])])) {
					$w += $cw[ord($s[$i])];
				}
				if (($textvar & TextVars::FC_KERNING) && $i > 0) { // mPDF 5.7.1
					if (isset($this->CurrentFont['kerninfo'][$s[($i - 1)]][$s[$i]])) {
						$kerning += $this->CurrentFont['kerninfo'][$s[($i - 1)]][$s[$i]];
					}
				}
			}
		}
		unset($cw);
		if ($textvar & TextVars::FC_KERNING) {
			$w += $kerning;
		} // mPDF 5.7.1
		$w *= ($this->FontSize / 1000);
		$w += (($nb_carac + $nb_spaces) * $this->fixedlSpacing) + ($nb_spaces * $this->minwSpacing);
		$w += $kashida / Mpdf::SCALE;

		return ($w);
	}

	function SetLineWidth($width)
	{
		// Set line width
		$this->LineWidth = $width;
		$lwout = (sprintf('%.3F w', $width * Mpdf::SCALE));
		if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['LineWidth']) && $this->pageoutput[$this->page]['LineWidth'] != $lwout) || !isset($this->pageoutput[$this->page]['LineWidth']))) {
			$this->writer->write($lwout);
		}
		$this->pageoutput[$this->page]['LineWidth'] = $lwout;
	}

	function Line($x1, $y1, $x2, $y2)
	{
		// Draw a line
		$this->writer->write(sprintf('%.3F %.3F m %.3F %.3F l S', $x1 * Mpdf::SCALE, ($this->h - $y1) * Mpdf::SCALE, $x2 * Mpdf::SCALE, ($this->h - $y2) * Mpdf::SCALE));
	}

	function Arrow($x1, $y1, $x2, $y2, $headsize = 3, $fill = 'B', $angle = 25)
	{
		// F == fill // S == stroke // B == stroke and fill
		// angle = splay of arrowhead - 1 - 89 degrees
		if ($fill == 'F') {
			$fill = 'f';
		} elseif ($fill == 'FD' or $fill == 'DF' or $fill == 'B') {
			$fill = 'B';
		} else {
			$fill = 'S';
		}
		$a = atan2(($y2 - $y1), ($x2 - $x1));
		$b = $a + deg2rad($angle);
		$c = $a - deg2rad($angle);
		$x3 = $x2 - ($headsize * cos($b));
		$y3 = $this->h - ($y2 - ($headsize * sin($b)));
		$x4 = $x2 - ($headsize * cos($c));
		$y4 = $this->h - ($y2 - ($headsize * sin($c)));

		$x5 = $x3 - ($x3 - $x4) / 2; // mid point of base of arrowhead - to join arrow line to
		$y5 = $y3 - ($y3 - $y4) / 2;

		$s = '';
		$s .= sprintf('%.3F %.3F m %.3F %.3F l S', $x1 * Mpdf::SCALE, ($this->h - $y1) * Mpdf::SCALE, $x5 * Mpdf::SCALE, $y5 * Mpdf::SCALE);
		$this->writer->write($s);

		$s = '';
		$s .= sprintf('%.3F %.3F m %.3F %.3F l %.3F %.3F l %.3F %.3F l %.3F %.3F l ', $x5 * Mpdf::SCALE, $y5 * Mpdf::SCALE, $x3 * Mpdf::SCALE, $y3 * Mpdf::SCALE, $x2 * Mpdf::SCALE, ($this->h - $y2) * Mpdf::SCALE, $x4 * Mpdf::SCALE, $y4 * Mpdf::SCALE, $x5 * Mpdf::SCALE, $y5 * Mpdf::SCALE);
		$s .= $fill;
		$this->writer->write($s);
	}

	function Rect($x, $y, $w, $h, $style = '')
	{
		// Draw a rectangle
		if ($style == 'F') {
			$op = 'f';
		} elseif ($style == 'FD' or $style == 'DF') {
			$op = 'B';
		} else {
			$op = 'S';
		}
		$this->writer->write(sprintf('%.3F %.3F %.3F %.3F re %s', $x * Mpdf::SCALE, ($this->h - $y) * Mpdf::SCALE, $w * Mpdf::SCALE, -$h * Mpdf::SCALE, $op));
	}

	function AddFontDirectory($directory)
	{
		$this->fontDir[] = $directory;
		$this->fontFileFinder->setDirectories($this->fontDir);
	}

	function AddFont($family, $style = '')
	{
		if (empty($family)) {
			return;
		}

		$family = strtolower($family);
		$style = strtoupper($style);
		$style = str_replace('U', '', $style);

		if ($style == 'IB') {
			$style = 'BI';
		}

		$fontkey = $family . $style;

		// check if the font has been already added
		if (isset($this->fonts[$fontkey])) {
			return;
		}

		/* -- CJK-FONTS -- */
		if (in_array($family, $this->available_CJK_fonts)) {
			if (empty($this->Big5_widths)) {
				require __DIR__ . '/../data/CJKdata.php';
			}
			$this->AddCJKFont($family); // don't need to add style
			return;
		}
		/* -- END CJK-FONTS -- */

		if ($this->usingCoreFont) {
			throw new \Mpdf\MpdfException("mPDF Error - problem with Font management");
		}

		$stylekey = $style;
		if (!$style) {
			$stylekey = 'R';
		}

		if (!isset($this->fontdata[$family][$stylekey]) || !$this->fontdata[$family][$stylekey]) {
			throw new \Mpdf\MpdfException(sprintf('Font "%s%s%s" is not supported', $family, $style ? ' - ' : '', $style));
		}

		/* Setup defaults */
		$font = [
			'name' => '',
			'type' => '',
			'desc' => '',
			'panose' => '',
			'unitsPerEm' => '',
			'up' => '',
			'ut' => '',
			'strs' => '',
			'strp' => '',
			'sip' => false,
			'smp' => false,
			'useOTL' => 0,
			'fontmetrics' => '',
			'haskerninfo' => false,
			'haskernGPOS' => false,
			'hassmallcapsGSUB' => false,
			'BMPselected' => false,
			'GSUBScriptLang' => [],
			'GSUBFeatures' => [],
			'GSUBLookups' => [],
			'GPOSScriptLang' => [],
			'GPOSFeatures' => [],
			'GPOSLookups' => [],
			'rtlPUAstr' => '',
		];

		$fontCacheFilename = $fontkey . '.mtx.json';
		if ($this->fontCache->jsonHas($fontCacheFilename)) {
			$font = $this->fontCache->jsonLoad($fontCacheFilename);
		}

		$ttffile = $this->fontFileFinder->findFontFile($this->fontdata[$family][$stylekey]);
		$ttfstat = stat($ttffile);

		$TTCfontID = isset($this->fontdata[$family]['TTCfontID'][$stylekey]) ? isset($this->fontdata[$family]['TTCfontID'][$stylekey]) : 0;
		$fontUseOTL = isset($this->fontdata[$family]['useOTL']) ? $this->fontdata[$family]['useOTL'] : false;
		$BMPonly = in_array($family, $this->BMPonly) ? true : false;

		$regenerate = false;
		if ($BMPonly && !$font['BMPselected']) {
			$regenerate = true;
		} elseif (!$BMPonly && $font['BMPselected']) {
			$regenerate = true;
		}

		if ($fontUseOTL && $font['useOTL'] != $fontUseOTL) {
			$regenerate = true;
			$font['useOTL'] = $fontUseOTL;
		} elseif (!$fontUseOTL && $font['useOTL']) {
			$regenerate = true;
			$font['useOTL'] = 0;
		}

		if ($this->fontDescriptor != $font['fontmetrics']) {
			$regenerate = true;
		} // mPDF 6

		if (empty($font['name']) || $font['originalsize'] != $ttfstat['size'] || $regenerate) {
			$generator = new MetricsGenerator($this->fontCache, $this->fontDescriptor);

			$generator->generateMetrics(
				$ttffile,
				$ttfstat,
				$fontkey,
				$TTCfontID,
				$this->debugfonts,
				$BMPonly,
				$font['useOTL'],
				$fontUseOTL
			);

			$font = $this->fontCache->jsonLoad($fontCacheFilename);
			$cw = $this->fontCache->load($fontkey . '.cw.dat');
			$glyphIDtoUni = $this->fontCache->load($fontkey . '.gid.dat');
		} else {
			if ($this->fontCache->has($fontkey . '.cw.dat')) {
				$cw = $this->fontCache->load($fontkey . '.cw.dat');
			}

			if ($this->fontCache->has($fontkey . '.gid.dat')) {
				$glyphIDtoUni = $this->fontCache->load($fontkey . '.gid.dat');
			}
		}

		if (isset($this->fontdata[$family]['sip-ext']) && $this->fontdata[$family]['sip-ext']) {
			$sipext = $this->fontdata[$family]['sip-ext'];
		} else {
			$sipext = '';
		}

		// Override with values from config_font.php
		if (isset($this->fontdata[$family]['Ascent']) && $this->fontdata[$family]['Ascent']) {
			$desc['Ascent'] = $this->fontdata[$family]['Ascent'];
		}
		if (isset($this->fontdata[$family]['Descent']) && $this->fontdata[$family]['Descent']) {
			$desc['Descent'] = $this->fontdata[$family]['Descent'];
		}
		if (isset($this->fontdata[$family]['Leading']) && $this->fontdata[$family]['Leading']) {
			$desc['Leading'] = $this->fontdata[$family]['Leading'];
		}

		$i = count($this->fonts) + $this->extraFontSubsets + 1;

		$this->fonts[$fontkey] = [
			'i' => $i,
			'name' => $font['name'],
			'type' => $font['type'],
			'desc' => $font['desc'],
			'panose' => $font['panose'],
			'unitsPerEm' => $font['unitsPerEm'],
			'up' => $font['up'],
			'ut' => $font['ut'],
			'strs' => $font['strs'],
			'strp' => $font['strp'],
			'cw' => $cw,
			'ttffile' => $ttffile,
			'fontkey' => $fontkey,
			'used' => false,
			'sip' => $font['sip'],
			'sipext' => $sipext,
			'smp' => $font['smp'],
			'TTCfontID' => $TTCfontID,
			'useOTL' => $fontUseOTL,
			'useKashida' => (isset($this->fontdata[$family]['useKashida']) ? $this->fontdata[$family]['useKashida'] : false),
			'GSUBScriptLang' => $font['GSUBScriptLang'],
			'GSUBFeatures' => $font['GSUBFeatures'],
			'GSUBLookups' => $font['GSUBLookups'],
			'GPOSScriptLang' => $font['GPOSScriptLang'],
			'GPOSFeatures' => $font['GPOSFeatures'],
			'GPOSLookups' => $font['GPOSLookups'],
			'rtlPUAstr' => $font['rtlPUAstr'],
			'glyphIDtoUni' => $glyphIDtoUni,
			'haskerninfo' => $font['haskerninfo'],
			'haskernGPOS' => $font['haskernGPOS'],
			'hassmallcapsGSUB' => $font['hassmallcapsGSUB'],
		];


		if (!$font['sip'] && !$font['smp']) {
			$subsetRange = range(32, 127);
			$this->fonts[$fontkey]['subset'] = array_combine($subsetRange, $subsetRange);
		} else {
			$this->fonts[$fontkey]['subsets'] = [0 => range(0, 127)];
			$this->fonts[$fontkey]['subsetfontids'] = [$i];
		}

		if ($font['haskerninfo']) {
			$this->fonts[$fontkey]['kerninfo'] = $font['kerninfo'];
		}

		$this->FontFiles[$fontkey] = [
			'length1' => $font['originalsize'],
			'type' => 'TTF',
			'ttffile' => $ttffile,
			'sip' => $font['sip'],
			'smp' => $font['smp'],
		];

		unset($cw);
	}

	function SetFont($family, $style = '', $size = 0, $write = true, $forcewrite = false)
	{
		$family = strtolower($family);

		if (!$this->onlyCoreFonts) {
			if ($family == 'sans' || $family == 'sans-serif') {
				$family = $this->sans_fonts[0];
			}
			if ($family == 'serif') {
				$family = $this->serif_fonts[0];
			}
			if ($family == 'mono' || $family == 'monospace') {
				$family = $this->mono_fonts[0];
			}
		}

		if (isset($this->fonttrans[$family]) && $this->fonttrans[$family]) {
			$family = $this->fonttrans[$family];
		}

		if ($family == '') {
			if ($this->FontFamily) {
				$family = $this->FontFamily;
			} elseif ($this->default_font) {
				$family = $this->default_font;
			} else {
				throw new \Mpdf\MpdfException("No font or default font set!");
			}
		}

		$this->ReqFontStyle = $style; // required or requested style - used later for artificial bold/italic

		if (($family == 'csymbol') || ($family == 'czapfdingbats') || ($family == 'ctimes') || ($family == 'ccourier') || ($family == 'chelvetica')) {
			if ($this->PDFA || $this->PDFX) {
				if ($family == 'csymbol' || $family == 'czapfdingbats') {
					throw new \Mpdf\MpdfException("Symbol and Zapfdingbats cannot be embedded in mPDF (required for PDFA1-b or PDFX/1-a).");
				}
				if ($family == 'ctimes' || $family == 'ccourier' || $family == 'chelvetica') {
					if (($this->PDFA && !$this->PDFAauto) || ($this->PDFX && !$this->PDFXauto)) {
						$this->PDFAXwarnings[] = "Core Adobe font " . ucfirst($family) . " cannot be embedded in mPDF, which is required for PDFA1-b or PDFX/1-a. (Embedded font will be substituted.)";
					}
					if ($family == 'chelvetica') {
						$family = 'sans';
					}
					if ($family == 'ctimes') {
						$family = 'serif';
					}
					if ($family == 'ccourier') {
						$family = 'mono';
					}
				}
				$this->usingCoreFont = false;
			} else {
				$this->usingCoreFont = true;
			}
			if ($family == 'csymbol' || $family == 'czapfdingbats') {
				$style = '';
			}
		} else {
			$this->usingCoreFont = false;
		}

		// mPDF 5.7.1
		if ($style) {
			$style = strtoupper($style);
			if ($style == 'IB') {
				$style = 'BI';
			}
		}
		if ($size == 0) {
			$size = $this->FontSizePt;
		}

		$fontkey = $family . $style;

		$stylekey = $style;
		if (!$stylekey) {
			$stylekey = "R";
		}

		if (!$this->onlyCoreFonts && !$this->usingCoreFont) {
			if (!isset($this->fonts[$fontkey]) || count($this->default_available_fonts) != count($this->available_unifonts)) { // not already added

				/* -- CJK-FONTS -- */
				if (in_array($fontkey, $this->available_CJK_fonts)) {
					if (!isset($this->fonts[$fontkey])) { // already added
						if (empty($this->Big5_widths)) {
							require __DIR__ . '/../data/CJKdata.php';
						}
						$this->AddCJKFont($family); // don't need to add style
					}
				} else { // Test to see if requested font/style is available - or substitute /* -- END CJK-FONTS -- */
					if (!in_array($fontkey, $this->available_unifonts)) {
						// If font[nostyle] exists - set it
						if (in_array($family, $this->available_unifonts)) {
							$style = '';
						} // elseif only one font available - set it (assumes if only one font available it will not have a style)
						elseif (count($this->available_unifonts) == 1) {
							$family = $this->available_unifonts[0];
							$style = '';
						} else {
							$found = 0;
							// else substitute font of similar type
							if (in_array($family, $this->sans_fonts)) {
								$i = array_intersect($this->sans_fonts, $this->available_unifonts);
								if (count($i)) {
									$i = array_values($i);
									// with requested style if possible
									if (!in_array(($i[0] . $style), $this->available_unifonts)) {
										$style = '';
									}
									$family = $i[0];
									$found = 1;
								}
							} elseif (in_array($family, $this->serif_fonts)) {
								$i = array_intersect($this->serif_fonts, $this->available_unifonts);
								if (count($i)) {
									$i = array_values($i);
									// with requested style if possible
									if (!in_array(($i[0] . $style), $this->available_unifonts)) {
										$style = '';
									}
									$family = $i[0];
									$found = 1;
								}
							} elseif (in_array($family, $this->mono_fonts)) {
								$i = array_intersect($this->mono_fonts, $this->available_unifonts);
								if (count($i)) {
									$i = array_values($i);
									// with requested style if possible
									if (!in_array(($i[0] . $style), $this->available_unifonts)) {
										$style = '';
									}
									$family = $i[0];
									$found = 1;
								}
							}

							if (!$found) {
								// set first available font
								$fs = $this->available_unifonts[0];
								preg_match('/^([a-z_0-9\-]+)([BI]{0,2})$/', $fs, $fas); // Allow "-"
								// with requested style if possible
								$ws = $fas[1] . $style;
								if (in_array($ws, $this->available_unifonts)) {
									$family = $fas[1]; // leave $style as is
								} elseif (in_array($fas[1], $this->available_unifonts)) {
									// or without style
									$family = $fas[1];
									$style = '';
								} else {
									// or with the style specified
									$family = $fas[1];
									$style = $fas[2];
								}
							}
						}
						$fontkey = $family . $style;
					}
				}
			}

			// try to add font (if not already added)
			$this->AddFont($family, $style);

			// Test if font is already selected
			if ($this->FontFamily == $family && $this->FontFamily == $this->currentfontfamily && $this->FontStyle == $style && $this->FontStyle == $this->currentfontstyle && $this->FontSizePt == $size && $this->FontSizePt == $this->currentfontsize && !$forcewrite) {
				return $family;
			}

			$fontkey = $family . $style;

			// Select it
			$this->FontFamily = $family;
			$this->FontStyle = $style;
			$this->FontSizePt = $size;
			$this->FontSize = $size / Mpdf::SCALE;
			$this->CurrentFont = &$this->fonts[$fontkey];
			if ($write) {
				$fontout = (sprintf('BT /F%d %.3F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
				if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['Font']) && $this->pageoutput[$this->page]['Font'] != $fontout) || !isset($this->pageoutput[$this->page]['Font']))) {
					$this->writer->write($fontout);
				}
				$this->pageoutput[$this->page]['Font'] = $fontout;
			}

			// Added - currentfont (lowercase) used in HTML2PDF
			$this->currentfontfamily = $family;
			$this->currentfontsize = $size;
			$this->currentfontstyle = $style;
			$this->setMBencoding('UTF-8');
		} else {  // if using core fonts
			if ($this->PDFA || $this->PDFX) {
				throw new \Mpdf\MpdfException('Core Adobe fonts cannot be embedded in mPDF (required for PDFA1-b or PDFX/1-a) - cannot use option to use core fonts.');
			}
			$this->setMBencoding('windows-1252');

			// Test if font is already selected
			if (($this->FontFamily == $family) and ( $this->FontStyle == $style) and ( $this->FontSizePt == $size) && !$forcewrite) {
				return $family;
			}

			if (!isset($this->CoreFonts[$fontkey])) {
				if (in_array($family, $this->serif_fonts)) {
					$family = 'ctimes';
				} elseif (in_array($family, $this->mono_fonts)) {
					$family = 'ccourier';
				} else {
					$family = 'chelvetica';
				}
				$this->usingCoreFont = true;
				$fontkey = $family . $style;
			}

			if (!isset($this->fonts[$fontkey])) {
				// STANDARD CORE FONTS
				if (isset($this->CoreFonts[$fontkey])) {
					// Load metric file
					$file = $family;
					if ($family == 'ctimes' || $family == 'chelvetica' || $family == 'ccourier') {
						$file .= strtolower($style);
					}
					require __DIR__ . '/../data/font/' . $file . '.php';
					if (!isset($cw)) {
						throw new \Mpdf\MpdfException(sprintf('Could not include font metric file "%s"', $file));
					}
					$i = count($this->fonts) + $this->extraFontSubsets + 1;
					$this->fonts[$fontkey] = ['i' => $i, 'type' => 'core', 'name' => $this->CoreFonts[$fontkey], 'desc' => $desc, 'up' => $up, 'ut' => $ut, 'cw' => $cw];
					if ($this->useKerning && isset($kerninfo)) {
						$this->fonts[$fontkey]['kerninfo'] = $kerninfo;
					}
				} else {
					throw new \Mpdf\MpdfException(sprintf('Font %s not defined', $fontkey));
				}
			}

			// Test if font is already selected
			if (($this->FontFamily == $family) and ( $this->FontStyle == $style) and ( $this->FontSizePt == $size) && !$forcewrite) {
				return $family;
			}
			// Select it
			$this->FontFamily = $family;
			$this->FontStyle = $style;
			$this->FontSizePt = $size;
			$this->FontSize = $size / Mpdf::SCALE;
			$this->CurrentFont = &$this->fonts[$fontkey];
			if ($write) {
				$fontout = (sprintf('BT /F%d %.3F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
				if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['Font']) && $this->pageoutput[$this->page]['Font'] != $fontout) || !isset($this->pageoutput[$this->page]['Font']))) {
					$this->writer->write($fontout);
				}
				$this->pageoutput[$this->page]['Font'] = $fontout;
			}
			// Added - currentfont (lowercase) used in HTML2PDF
			$this->currentfontfamily = $family;
			$this->currentfontsize = $size;
			$this->currentfontstyle = $style;
		}

		return $family;
	}

	function SetFontSize($size, $write = true)
	{
		// Set font size in points
		if ($this->FontSizePt == $size) {
			return;
		}
		$this->FontSizePt = $size;
		$this->FontSize = $size / Mpdf::SCALE;
		$this->currentfontsize = $size;
		if ($write) {
			$fontout = (sprintf('BT /F%d %.3F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
			// Edited mPDF 3.0
			if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['Font']) && $this->pageoutput[$this->page]['Font'] != $fontout) || !isset($this->pageoutput[$this->page]['Font']))) {
				$this->writer->write($fontout);
			}
			$this->pageoutput[$this->page]['Font'] = $fontout;
		}
	}

	function AddLink()
	{
		// Create a new internal link
		$n = count($this->links) + 1;
		$this->links[$n] = [0, 0];
		return $n;
	}

	function SetLink($link, $y = 0, $page = -1)
	{
		// Set destination of internal link
		if ($y == -1) {
			$y = $this->y;
		}
		if ($page == -1) {
			$page = $this->page;
		}
		$this->links[$link] = [$page, $y];
	}

	function Link($x, $y, $w, $h, $link)
	{
		$l = [$x * Mpdf::SCALE, $this->hPt - $y * Mpdf::SCALE, $w * Mpdf::SCALE, $h * Mpdf::SCALE, $link];
		if ($this->keep_block_together) { // don't write yet
			return;
		} elseif ($this->table_rotate) { // *TABLES*
			$this->tbrot_Links[$this->page][] = $l; // *TABLES*
			return; // *TABLES*
		} // *TABLES*
		elseif ($this->kwt) {
			$this->kwt_Links[$this->page][] = $l;
			return;
		}

		if ($this->writingHTMLheader || $this->writingHTMLfooter) {
			$this->HTMLheaderPageLinks[] = $l;
			return;
		}
		// Put a link on the page
		$this->PageLinks[$this->page][] = $l;
		// Save cross-reference to Column buffer
		$ref = count($this->PageLinks[$this->page]) - 1; // *COLUMNS*
		$this->columnLinks[$this->CurrCol][(int) $this->x][(int) $this->y] = $ref; // *COLUMNS*
	}

	function Text($x, $y, $txt, $OTLdata = [], $textvar = 0, $aixextra = '', $coordsys = '', $return = false)
	{
		// Output (or return) a string
		// Called (internally) by Watermark() & _tableWrite() [rotated cells] & TableHeaderFooter() & WriteText()
		// Called also from classes/svg.php
		// Expects Font to be set
		// Expects input to be mb_encoded if necessary and RTL reversed & OTL processed
		// ARTIFICIAL BOLD AND ITALIC
		$s = 'q ';
		if ($this->falseBoldWeight && strpos($this->ReqFontStyle, "B") !== false && strpos($this->FontStyle, "B") === false) {
			$s .= '2 Tr 1 J 1 j ';
			$s .= sprintf('%.3F w ', ($this->FontSize / 130) * Mpdf::SCALE * $this->falseBoldWeight);
			$tc = strtoupper($this->TextColor); // change 0 0 0 rg to 0 0 0 RG
			if ($this->FillColor != $tc) {
				$s .= $tc . ' ';
			}  // stroke (outline) = same colour as text(fill)
		}
		if (strpos($this->ReqFontStyle, "I") !== false && strpos($this->FontStyle, "I") === false) {
			$aix = '1 0 0.261799 1 %.3F %.3F Tm';
		} else {
			$aix = '%.3F %.3F Td';
		}

		$aix = $aixextra . $aix;

		if ($this->ColorFlag) {
			$s .= $this->TextColor . ' ';
		}

		$this->CurrentFont['used'] = true;

		if ($this->usingCoreFont) {
			$txt2 = str_replace(chr(160), chr(32), $txt);
		} else {
			$txt2 = str_replace(chr(194) . chr(160), chr(32), $txt);
		}

		$px = $x;
		$py = $y;
		if ($coordsys != 'SVG') {
			$px = $x * Mpdf::SCALE;
			$py = ($this->h - $y) * Mpdf::SCALE;
		}


		/** ************** SIMILAR TO Cell() ************************ */

		// IF corefonts AND NOT SmCaps AND NOT Kerning
		// Just output text
		if ($this->usingCoreFont && !($textvar & TextVars::FC_SMALLCAPS) && !($textvar & TextVars::FC_KERNING)) {
			$txt2 = $this->writer->escape($txt2);
			$s .= sprintf('BT ' . $aix . ' (%s) Tj ET', $px, $py, $txt2);
		} // IF NOT corefonts [AND NO wordspacing] AND NOT SIP/SMP AND NOT SmCaps AND NOT Kerning AND NOT OTL
		// Just output text
		elseif (!$this->usingCoreFont && !($textvar & TextVars::FC_SMALLCAPS) && !($textvar & TextVars::FC_KERNING) && !(isset($this->CurrentFont['useOTL']) && ($this->CurrentFont['useOTL'] & 0xFF) && !empty($OTLdata['GPOSinfo']))) {
			// IF SIP/SMP
			if ($this->CurrentFont['sip'] || $this->CurrentFont['smp']) {
				$txt2 = $this->UTF8toSubset($txt2);
				$s .=sprintf('BT ' . $aix . ' %s Tj ET', $px, $py, $txt2);
			} // NOT SIP/SMP
			else {
				$txt2 = $this->writer->utf8ToUtf16BigEndian($txt2, false);
				$txt2 = $this->writer->escape($txt2);
				$s .=sprintf('BT ' . $aix . ' (%s) Tj ET', $px, $py, $txt2);
			}
		} // IF NOT corefonts [AND IS wordspacing] AND NOT SIP AND NOT SmCaps AND NOT Kerning AND NOT OTL
		// Not required here (cf. Cell() )
		// ELSE (IF SmCaps || Kerning || OTL) [corefonts or not corefonts; SIP or SMP or BMP]
		else {
			$s .= $this->applyGPOSpdf($txt2, $aix, $px, $py, $OTLdata, $textvar);
		}
		/*         * ************** END ************************ */

		$s .= ' ';

		if (($textvar & TextVars::FD_UNDERLINE) && $txt != '') { // mPDF 5.7.1
			$c = strtoupper($this->TextColor); // change 0 0 0 rg to 0 0 0 RG
			if ($this->FillColor != $c) {
				$s.= ' ' . $c . ' ';
			}
			if (isset($this->CurrentFont['up']) && $this->CurrentFont['up']) {
				$up = $this->CurrentFont['up'];
			} else {
				$up = -100;
			}
			$adjusty = (-$up / 1000 * $this->FontSize);
			if (isset($this->CurrentFont['ut']) && $this->CurrentFont['ut']) {
				$ut = $this->CurrentFont['ut'] / 1000 * $this->FontSize;
			} else {
				$ut = 60 / 1000 * $this->FontSize;
			}
			$olw = $this->LineWidth;
			$s .= ' ' . (sprintf(' %.3F w', $ut * Mpdf::SCALE));
			$s .= ' ' . $this->_dounderline($x, $y + $adjusty, $txt, $OTLdata, $textvar);
			$s .= ' ' . (sprintf(' %.3F w', $olw * Mpdf::SCALE));
			if ($this->FillColor != $c) {
				$s.= ' ' . $this->FillColor . ' ';
			}
		}
		// STRIKETHROUGH
		if (($textvar & TextVars::FD_LINETHROUGH) && $txt != '') { // mPDF 5.7.1
			$c = strtoupper($this->TextColor); // change 0 0 0 rg to 0 0 0 RG
			if ($this->FillColor != $c) {
				$s.= ' ' . $c . ' ';
			}
			// Superscript and Subscript Y coordinate adjustment (now for striked-through texts)
			if (isset($this->CurrentFont['desc']['CapHeight']) && $this->CurrentFont['desc']['CapHeight']) {
				$ch = $this->CurrentFont['desc']['CapHeight'];
			} else {
				$ch = 700;
			}
			$adjusty = (-$ch / 1000 * $this->FontSize) * 0.35;
			if (isset($this->CurrentFont['ut']) && $this->CurrentFont['ut']) {
				$ut = $this->CurrentFont['ut'] / 1000 * $this->FontSize;
			} else {
				$ut = 60 / 1000 * $this->FontSize;
			}
			$olw = $this->LineWidth;
			$s .= ' ' . (sprintf(' %.3F w', $ut * Mpdf::SCALE));
			$s .= ' ' . $this->_dounderline($x, $y + $adjusty, $txt, $OTLdata, $textvar);
			$s .= ' ' . (sprintf(' %.3F w', $olw * Mpdf::SCALE));
			if ($this->FillColor != $c) {
				$s.= ' ' . $this->FillColor . ' ';
			}
		}
		$s .= 'Q';

		if ($return) {
			return $s . " \n";
		}
		$this->writer->write($s);
	}

	/* -- DIRECTW -- */

	function WriteText($x, $y, $txt)
	{
		// Output a string using Text() but does encoding and text reversing of RTL
		$txt = $this->purify_utf8_text($txt);
		if ($this->text_input_as_HTML) {
			$txt = $this->all_entities_to_utf8($txt);
		}
		if ($this->usingCoreFont) {
			$txt = mb_convert_encoding($txt, $this->mb_enc, 'UTF-8');
		}

		// DIRECTIONALITY
		if (preg_match("/([" . $this->pregRTLchars . "])/u", $txt)) {
			$this->biDirectional = true;
		} // *OTL*

		$textvar = 0;
		$save_OTLtags = $this->OTLtags;
		$this->OTLtags = [];
		if ($this->useKerning) {
			if ($this->CurrentFont['haskernGPOS']) {
				$this->OTLtags['Plus'] .= ' kern';
			} else {
				$textvar = ($textvar | TextVars::FC_KERNING);
			}
		}

		/* -- OTL -- */
		// Use OTL OpenType Table Layout - GSUB & GPOS
		if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
			$txt = $this->otl->applyOTL($txt, $this->CurrentFont['useOTL']);
			$OTLdata = $this->otl->OTLdata;
		}
		/* -- END OTL -- */
		$this->OTLtags = $save_OTLtags;

		$this->magic_reverse_dir($txt, $this->directionality, $OTLdata);

		$this->Text($x, $y, $txt, $OTLdata, $textvar);
	}

	function WriteCell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = 0, $link = '', $currentx = 0)
	{
		// Output a cell using Cell() but does encoding and text reversing of RTL
		$txt = $this->purify_utf8_text($txt);
		if ($this->text_input_as_HTML) {
			$txt = $this->all_entities_to_utf8($txt);
		}
		if ($this->usingCoreFont) {
			$txt = mb_convert_encoding($txt, $this->mb_enc, 'UTF-8');
		}
		// DIRECTIONALITY
		if (preg_match("/([" . $this->pregRTLchars . "])/u", $txt)) {
			$this->biDirectional = true;
		} // *OTL*

		$textvar = 0;
		$save_OTLtags = $this->OTLtags;
		$this->OTLtags = [];
		if ($this->useKerning) {
			if ($this->CurrentFont['haskernGPOS']) {
				$this->OTLtags['Plus'] .= ' kern';
			} else {
				$textvar = ($textvar | TextVars::FC_KERNING);
			}
		}

		/* -- OTL -- */
		// Use OTL OpenType Table Layout - GSUB & GPOS
		if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
			$txt = $this->otl->applyOTL($txt, $this->CurrentFont['useOTL']);
			$OTLdata = $this->otl->OTLdata;
		}
		/* -- END OTL -- */
		$this->OTLtags = $save_OTLtags;

		$this->magic_reverse_dir($txt, $this->directionality, $OTLdata);

		$this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link, $currentx, 0, 0, 'M', 0, false, $OTLdata, $textvar);
	}

	/* -- END DIRECTW -- */

	function ResetSpacing()
	{
		if ($this->ws != 0) {
			$this->writer->write('BT 0 Tw ET');
		}
		$this->ws = 0;
		if ($this->charspacing != 0) {
			$this->writer->write('BT 0 Tc ET');
		}
		$this->charspacing = 0;
	}

	function SetSpacing($cs, $ws)
	{
		if (intval($cs * 1000) == 0) {
			$cs = 0;
		}
		if ($cs) {
			$this->writer->write(sprintf('BT %.3F Tc ET', $cs));
		} elseif ($this->charspacing != 0) {
			$this->writer->write('BT 0 Tc ET');
		}
		$this->charspacing = $cs;
		if (intval($ws * 1000) == 0) {
			$ws = 0;
		}
		if ($ws) {
			$this->writer->write(sprintf('BT %.3F Tw ET', $ws));
		} elseif ($this->ws != 0) {
			$this->writer->write('BT 0 Tw ET');
		}
		$this->ws = $ws;
	}

	// WORD SPACING
	function GetJspacing($nc, $ns, $w, $inclCursive, &$cOTLdata)
	{
		$kashida_present = false;
		$kashida_space = 0;
		if ($w > 0 && $inclCursive && isset($this->CurrentFont['useKashida']) && $this->CurrentFont['useKashida'] && !empty($cOTLdata)) {
			for ($c = 0; $c < count($cOTLdata); $c++) {
				for ($i = 0; $i < strlen($cOTLdata[$c]['group']); $i++) {
					if (isset($cOTLdata[$c]['GPOSinfo'][$i]['kashida']) && $cOTLdata[$c]['GPOSinfo'][$i]['kashida'] > 0) {
						$kashida_present = true;
						break 2;
					}
				}
			}
		}

		if ($kashida_present) {
			$k_ctr = 0;  // Number of kashida points
			$k_total = 0;  // Total of kashida values (priority)
			// Reset word
			$max_kashida_in_word = 0;
			$last_kashida_in_word = -1;

			for ($c = 0; $c < count($cOTLdata); $c++) {
				for ($i = 0; $i < strlen($cOTLdata[$c]['group']); $i++) {
					if ($cOTLdata[$c]['group'][$i] == 'S') {
						// Save from last word
						if ($max_kashida_in_word) {
							$k_ctr++;
							$k_total = $max_kashida_in_word;
						}
						// Reset word
						$max_kashida_in_word = 0;
						$last_kashida_in_word = -1;
					}

					if (isset($cOTLdata[$c]['GPOSinfo'][$i]['kashida']) && $cOTLdata[$c]['GPOSinfo'][$i]['kashida'] > 0) {
						if ($max_kashida_in_word) {
							if ($cOTLdata[$c]['GPOSinfo'][$i]['kashida'] > $max_kashida_in_word) {
								$max_kashida_in_word = $cOTLdata[$c]['GPOSinfo'][$i]['kashida'];
								$cOTLdata[$c]['GPOSinfo'][$last_kashida_in_word]['kashida'] = 0;
								$last_kashida_in_word = $i;
							} else {
								$cOTLdata[$c]['GPOSinfo'][$i]['kashida'] = 0;
							}
						} else {
							$max_kashida_in_word = $cOTLdata[$c]['GPOSinfo'][$i]['kashida'];
							$last_kashida_in_word = $i;
						}
					}
				}
			}
			// Save from last word
			if ($max_kashida_in_word) {
				$k_ctr++;
				$k_total = $max_kashida_in_word;
			}

			// Number of kashida points = $k_ctr
			// $useKashida is a % value from CurrentFont/config_fonts.php
			// % ratio divided between word-spacing and kashida-spacing
			$kashida_space_ratio = intval($this->CurrentFont['useKashida']) / 100;


			$kashida_space = $w * $kashida_space_ratio;

			$tatw = $this->_getCharWidth($this->CurrentFont['cw'], 0x0640);
			// Only use kashida if each allocated kashida width is > 0.01 x width of a tatweel
			// Otherwise fontstretch is too small and errors
			// If not just leave to adjust word-spacing
			if ($tatw && (($kashida_space / $k_ctr) / $tatw) > 0.01) {
				for ($c = 0; $c < count($cOTLdata); $c++) {
					for ($i = 0; $i < strlen($cOTLdata[$c]['group']); $i++) {
						if (isset($cOTLdata[$c]['GPOSinfo'][$i]['kashida']) && $cOTLdata[$c]['GPOSinfo'][$i]['kashida'] > 0) {
							// At this point kashida is a number representing priority (higher number - higher priority)
							// We are now going to set it as an actual length
							// This shares it equally amongst words:
							$cOTLdata[$c]['GPOSinfo'][$i]['kashida_space'] = (1 / $k_ctr) * $kashida_space;
						}
					}
				}
				$w -= $kashida_space;
			}
		}

		$ws = 0;
		$charspacing = 0;
		$ww = $this->jSWord;
		$ncx = $nc - 1;
		if ($nc == 0) {
			return [0, 0, 0];
		} // Only word spacing allowed / possible
		elseif ($this->fixedlSpacing !== false || $inclCursive) {
			if ($ns) {
				$ws = $w / $ns;
			}
		} elseif ($nc == 1) {
			$charspacing = $w;
		} elseif (!$ns) {
			$charspacing = $w / ($ncx );
			if (($this->jSmaxChar > 0) && ($charspacing > $this->jSmaxChar)) {
				$charspacing = $this->jSmaxChar;
			}
		} elseif ($ns == ($ncx )) {
			$charspacing = $w / $ns;
		} else {
			if ($this->usingCoreFont) {
				$cs = ($w * (1 - $this->jSWord)) / ($ncx );
				if (($this->jSmaxChar > 0) && ($cs > $this->jSmaxChar)) {
					$cs = $this->jSmaxChar;
					$ww = 1 - (($cs * ($ncx )) / $w);
				}
				$charspacing = $cs;
				$ws = ($w * ($ww) ) / $ns;
			} else {
				$cs = ($w * (1 - $this->jSWord)) / ($ncx - $ns);
				if (($this->jSmaxChar > 0) && ($cs > $this->jSmaxChar)) {
					$cs = $this->jSmaxChar;
					$ww = 1 - (($cs * ($ncx - $ns)) / $w);
				}
				$charspacing = $cs;
				$ws = (($w * ($ww) ) / $ns) - $charspacing;
			}
		}
		return [$charspacing, $ws, $kashida_space];
	}

	/**
	 * Output a cell
	 *
	 * Expects input to be mb_encoded if necessary and RTL reversed
	 *
	 * @since mPDF 5.7.1
	 */
	function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = 0, $link = '', $currentx = 0, $lcpaddingL = 0, $lcpaddingR = 0, $valign = 'M', $spanfill = 0, $exactWidth = false, $OTLdata = false, $textvar = 0, $lineBox = false)
	{
		// NON_BREAKING SPACE
		if ($this->usingCoreFont) {
			$txt = str_replace(chr(160), chr(32), $txt);
		} else {
			$txt = str_replace(chr(194) . chr(160), chr(32), $txt);
		}

		$oldcolumn = $this->CurrCol;

		// Automatic page break
		// Allows PAGE-BREAK-AFTER = avoid to work
		if (isset($this->blk[$this->blklvl])) {
			$bottom = $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['margin_bottom'];
		} else {
			$bottom = 0;
		}

		if (!$this->tableLevel
			&& (
				($this->y + $this->divheight > $this->PageBreakTrigger)
				|| ($this->y + $h > $this->PageBreakTrigger)
				|| (
					$this->y + ($h * 2) + $bottom > $this->PageBreakTrigger
						&& $this->blk[$this->blklvl]['page_break_after_avoid']
				)
			)
			&& !$this->InFooter
			&& $this->AcceptPageBreak()
		) { // mPDF 5.7.2

			$x = $this->x; // Current X position

			// WORD SPACING
			$ws = $this->ws; // Word Spacing
			$charspacing = $this->charspacing; // Character Spacing
			$this->ResetSpacing();

			$this->AddPage($this->CurOrientation);

			// Added to correct for OddEven Margins
			$x += $this->MarginCorrection;
			if ($currentx) {
				$currentx += $this->MarginCorrection;
			}
			$this->x = $x;
			// WORD SPACING
			$this->SetSpacing($charspacing, $ws);
		}

		// Test: to put line through centre of cell: $this->Line($this->x,$this->y+($h/2),$this->x+50,$this->y+($h/2));
		// Test: to put border around cell as it is specified: $border='LRTB';

		/* -- COLUMNS -- */
		// COLS
		// COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			if ($currentx) {
				$currentx += $this->ChangeColumn * ($this->ColWidth + $this->ColGap);
			}
			$this->x += $this->ChangeColumn * ($this->ColWidth + $this->ColGap);
		}

		// COLUMNS Update/overwrite the lowest bottom of printing y value for a column
		if ($this->ColActive) {
			if ($h) {
				$this->ColDetails[$this->CurrCol]['bottom_margin'] = $this->y + $h;
			} else {
				$this->ColDetails[$this->CurrCol]['bottom_margin'] = $this->y + $this->divheight;
			}
		}
		/* -- END COLUMNS -- */


		if ($w == 0) {
			$w = $this->w - $this->rMargin - $this->x;
		}

		$s = '';
		if ($fill == 1 && $this->FillColor) {
			if ((isset($this->pageoutput[$this->page]['FillColor']) && $this->pageoutput[$this->page]['FillColor'] != $this->FillColor) || !isset($this->pageoutput[$this->page]['FillColor'])) {
				$s .= $this->FillColor . ' ';
			}
			$this->pageoutput[$this->page]['FillColor'] = $this->FillColor;
		}

		if ($lineBox && isset($lineBox['boxtop']) && $txt) { // i.e. always from WriteFlowingBlock/finishFlowingBlock (but not objects -
			// which only have $lineBox['top'] set)
			$boxtop = $this->y + $lineBox['boxtop'];
			$boxbottom = $this->y + $lineBox['boxbottom'];
			$glyphYorigin = $lineBox['glyphYorigin'];
			$baseline_shift = $lineBox['baseline-shift'];
			$bord_boxtop = $bg_boxtop = $boxtop = $boxtop - $baseline_shift;
			$bord_boxbottom = $bg_boxbottom = $boxbottom = $boxbottom - $baseline_shift;
			$bord_boxheight = $bg_boxheight = $boxheight = $boxbottom - $boxtop;

			// If inline element BACKGROUND has bounding box set by parent element:
			if (isset($lineBox['background-boxtop'])) {
				$bg_boxtop = $this->y + $lineBox['background-boxtop'] - $lineBox['background-baseline-shift'];
				$bg_boxbottom = $this->y + $lineBox['background-boxbottom'] - $lineBox['background-baseline-shift'];
				$bg_boxheight = $bg_boxbottom - $bg_boxtop;
			}
			// If inline element BORDER has bounding box set by parent element:
			if (isset($lineBox['border-boxtop'])) {
				$bord_boxtop = $this->y + $lineBox['border-boxtop'] - $lineBox['border-baseline-shift'];
				$bord_boxbottom = $this->y + $lineBox['border-boxbottom'] - $lineBox['border-baseline-shift'];
				$bord_boxheight = $bord_boxbottom - $bord_boxtop;
			}

		} else {

			$boxtop = $this->y;
			$boxheight = $h;
			$boxbottom = $this->y + $h;
			$baseline_shift = 0;

			if ($txt != '') {

				// FONT SIZE - this determines the baseline caculation
				$bfs = $this->FontSize;
				// Calculate baseline Superscript and Subscript Y coordinate adjustment
				$bfx = $this->baselineC;
				$baseline = $bfx * $bfs;

				if ($textvar & TextVars::FA_SUPERSCRIPT) {
					$baseline_shift = $this->textparam['text-baseline'];
				} elseif ($textvar & TextVars::FA_SUBSCRIPT) {
					$baseline_shift = $this->textparam['text-baseline'];
				} elseif ($this->bullet) {
					$baseline += ($bfx - 0.7) * $this->FontSize;
				}

				// Vertical align (for Images)
				if ($valign == 'T') {
					$va = (0.5 * $bfs * $this->normalLineheight);
				} elseif ($valign == 'B') {
					$va = $h - (0.5 * $bfs * $this->normalLineheight);
				} else {
					$va = 0.5 * $h;
				} // Middle

				// ONLY SET THESE IF WANT TO CONFINE BORDER +/- FILL TO FIT FONTSIZE - NOT FULL CELL AS IS ORIGINAL FUNCTION
				// spanfill or spanborder are set in FlowingBlock functions
				if ($spanfill || !empty($this->spanborddet) || $link != '') {
					$exth = 0.2; // Add to fontsize to increase height of background / link / border
					$boxtop = $this->y + $baseline + $va - ($this->FontSize * (1 + $exth / 2) * (0.5 + $bfx));
					$boxheight = $this->FontSize * (1 + $exth);
					$boxbottom = $boxtop + $boxheight;
				}

				$glyphYorigin = $baseline + $va;
			}

			$boxtop -= $baseline_shift;
			$boxbottom -= $baseline_shift;
			$bord_boxtop = $bg_boxtop = $boxtop;
			$bord_boxbottom = $bg_boxbottom = $boxbottom;
			$bord_boxheight = $bg_boxheight = $boxheight = $boxbottom - $boxtop;
		}

		$bbw = $tbw = $lbw = $rbw = 0; // Border widths
		if (!empty($this->spanborddet)) {

			if (!isset($this->spanborddet['B'])) {
				$this->spanborddet['B'] = ['s' => 0, 'style' => '', 'w' => 0];
			}

			if (!isset($this->spanborddet['T'])) {
				$this->spanborddet['T'] = ['s' => 0, 'style' => '', 'w' => 0];
			}

			if (!isset($this->spanborddet['L'])) {
				$this->spanborddet['L'] = ['s' => 0, 'style' => '', 'w' => 0];
			}

			if (!isset($this->spanborddet['R'])) {
				$this->spanborddet['R'] = ['s' => 0, 'style' => '', 'w' => 0];
			}

			$bbw = $this->spanborddet['B']['w'];
			$tbw = $this->spanborddet['T']['w'];
			$lbw = $this->spanborddet['L']['w'];
			$rbw = $this->spanborddet['R']['w'];
		}

		if ($fill == 1 || $border == 1 || !empty($this->spanborddet)) {

			if (!empty($this->spanborddet)) {

				if ($fill == 1) {
					$s .= sprintf('%.3F %.3F %.3F %.3F re f ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bg_boxtop + $tbw) * Mpdf::SCALE, ($w + $lbw + $rbw) * Mpdf::SCALE, (-$bg_boxheight - $tbw - $bbw) * Mpdf::SCALE);
				}

				$s.= ' q ';
				$dashon = 3;
				$dashoff = 3.5;
				$dot = 2.5;

				if ($tbw) {
					$short = 0;

					if ($this->spanborddet['T']['style'] == 'dashed') {
						$s .= sprintf(' 0 j 0 J [%.3F %.3F] 0 d ', $tbw * $dashon * Mpdf::SCALE, $tbw * $dashoff * Mpdf::SCALE);
					} elseif ($this->spanborddet['T']['style'] == 'dotted') {
						$s .= sprintf(' 1 j 1 J [%.3F %.3F] %.3F d ', 0.001, $tbw * $dot * Mpdf::SCALE, -$tbw / 2 * Mpdf::SCALE);
						$short = $tbw / 2;
					} else {
						$s .= ' 0 j 0 J [] 0 d ';
					}

					if ($this->spanborddet['T']['style'] != 'dotted') {
						$s .= 'q ';
						$s .= sprintf('%.3F %.3F m ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x + $w + $rbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x + $w) * Mpdf::SCALE, ($this->h - $bord_boxtop) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x) * Mpdf::SCALE, ($this->h - $bord_boxtop) * Mpdf::SCALE);
						$s .= ' h W n '; // Ends path no-op & Sets the clipping path
					}

					$c = $this->SetDColor($this->spanborddet['T']['c'], true);

					if ($this->spanborddet['T']['style'] == 'double') {
						$s .= sprintf(' %s %.3F w ', $c, $tbw / 3 * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw * 5 / 6) * Mpdf::SCALE, ($this->x + $w + $rbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw * 5 / 6) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw / 6) * Mpdf::SCALE, ($this->x + $w + $rbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw / 6) * Mpdf::SCALE);
					} elseif ($this->spanborddet['T']['style'] == 'dotted') {
						$s .= sprintf(' %s %.3F w ', $c, $tbw * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw / 2) * Mpdf::SCALE, ($this->x + $w + $rbw - $short) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw / 2) * Mpdf::SCALE);
					} else {
						$s .= sprintf(' %s %.3F w ', $c, $tbw * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw / 2) * Mpdf::SCALE, ($this->x + $w + $rbw - $short) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw / 2) * Mpdf::SCALE);
					}

					if ($this->spanborddet['T']['style'] != 'dotted') {
						$s .= ' Q ';
					}
				}
				if ($bbw) {

					$short = 0;
					if ($this->spanborddet['B']['style'] == 'dashed') {
						$s .= sprintf(' 0 j 0 J [%.3F %.3F] 0 d ', $bbw * $dashon * Mpdf::SCALE, $bbw * $dashoff * Mpdf::SCALE);
					} elseif ($this->spanborddet['B']['style'] == 'dotted') {
						$s .= sprintf(' 1 j 1 J [%.3F %.3F] %.3F d ', 0.001, $bbw * $dot * Mpdf::SCALE, -$bbw / 2 * Mpdf::SCALE);
						$short = $bbw / 2;
					} else {
						$s .= ' 0 j 0 J [] 0 d ';
					}

					if ($this->spanborddet['B']['style'] != 'dotted') {
						$s .= 'q ';
						$s .= sprintf('%.3F %.3F m ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x + $w + $rbw) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x + $w) * Mpdf::SCALE, ($this->h - $bord_boxbottom) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x) * Mpdf::SCALE, ($this->h - $bord_boxbottom) * Mpdf::SCALE);
						$s .= ' h W n '; // Ends path no-op & Sets the clipping path
					}

					$c = $this->SetDColor($this->spanborddet['B']['c'], true);

					if ($this->spanborddet['B']['style'] == 'double') {
						$s .= sprintf(' %s %.3F w ', $c, $bbw / 3 * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw / 6) * Mpdf::SCALE, ($this->x + $w + $rbw - $short) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw / 6) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw * 5 / 6) * Mpdf::SCALE, ($this->x + $w + $rbw - $short) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw * 5 / 6) * Mpdf::SCALE);
					} elseif ($this->spanborddet['B']['style'] == 'dotted') {
						$s .= sprintf(' %s %.3F w ', $c, $bbw * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw / 2) * Mpdf::SCALE, ($this->x + $w + $rbw - $short) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw / 2) * Mpdf::SCALE);
					} else {
						$s .= sprintf(' %s %.3F w ', $c, $bbw * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw / 2) * Mpdf::SCALE, ($this->x + $w + $rbw - $short) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw / 2) * Mpdf::SCALE);
					}

					if ($this->spanborddet['B']['style'] != 'dotted') {
						$s .= ' Q ';
					}
				}

				if ($lbw) {
					$short = 0;
					if ($this->spanborddet['L']['style'] == 'dashed') {
						$s .= sprintf(' 0 j 0 J [%.3F %.3F] 0 d ', $lbw * $dashon * Mpdf::SCALE, $lbw * $dashoff * Mpdf::SCALE);
					} elseif ($this->spanborddet['L']['style'] == 'dotted') {
						$s .= sprintf(' 1 j 1 J [%.3F %.3F] %.3F d ', 0.001, $lbw * $dot * Mpdf::SCALE, -$lbw / 2 * Mpdf::SCALE);
						$short = $lbw / 2;
					} else {
						$s .= ' 0 j 0 J [] 0 d ';
					}

					if ($this->spanborddet['L']['style'] != 'dotted') {
						$s .= 'q ';
						$s .= sprintf('%.3F %.3F m ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x) * Mpdf::SCALE, ($this->h - $bord_boxbottom) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x) * Mpdf::SCALE, ($this->h - $bord_boxtop) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE);
						$s .= ' h W n '; // Ends path no-op & Sets the clipping path
					}

					$c = $this->SetDColor($this->spanborddet['L']['c'], true);
					if ($this->spanborddet['L']['style'] == 'double') {
						$s .= sprintf(' %s %.3F w ', $c, $lbw / 3 * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw / 6) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE, ($this->x - $lbw / 6) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw + $short) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw * 5 / 6) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE, ($this->x - $lbw * 5 / 6) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw + $short) * Mpdf::SCALE);
					} elseif ($this->spanborddet['L']['style'] == 'dotted') {
						$s .= sprintf(' %s %.3F w ', $c, $lbw * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw / 2) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE, ($this->x - $lbw / 2) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw + $short) * Mpdf::SCALE);
					} else {
						$s .= sprintf(' %s %.3F w ', $c, $lbw * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw / 2) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE, ($this->x - $lbw / 2) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw + $short) * Mpdf::SCALE);
					}

					if ($this->spanborddet['L']['style'] != 'dotted') {
						$s .= ' Q ';
					}
				}

				if ($rbw) {

					$short = 0;
					if ($this->spanborddet['R']['style'] == 'dashed') {
						$s .= sprintf(' 0 j 0 J [%.3F %.3F] 0 d ', $rbw * $dashon * Mpdf::SCALE, $rbw * $dashoff * Mpdf::SCALE);
					} elseif ($this->spanborddet['R']['style'] == 'dotted') {
						$s .= sprintf(' 1 j 1 J [%.3F %.3F] %.3F d ', 0.001, $rbw * $dot * Mpdf::SCALE, -$rbw / 2 * Mpdf::SCALE);
						$short = $rbw / 2;
					} else {
						$s .= ' 0 j 0 J [] 0 d ';
					}

					if ($this->spanborddet['R']['style'] != 'dotted') {
						$s .= 'q ';
						$s .= sprintf('%.3F %.3F m ', ($this->x + $w + $rbw) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x + $w) * Mpdf::SCALE, ($this->h - $bord_boxbottom) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x + $w) * Mpdf::SCALE, ($this->h - $bord_boxtop) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x + $w + $rbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE);
						$s .= ' h W n '; // Ends path no-op & Sets the clipping path
					}

					$c = $this->SetDColor($this->spanborddet['R']['c'], true);
					if ($this->spanborddet['R']['style'] == 'double') {
						$s .= sprintf(' %s %.3F w ', $c, $rbw / 3 * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x + $w + $rbw / 6) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE, ($this->x + $w + $rbw / 6) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw + $short) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x + $w + $rbw * 5 / 6) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE, ($this->x + $w + $rbw * 5 / 6) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw + $short) * Mpdf::SCALE);
					} elseif ($this->spanborddet['R']['style'] == 'dotted') {
						$s .= sprintf(' %s %.3F w ', $c, $rbw * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x + $w + $rbw / 2) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE, ($this->x + $w + $rbw / 2) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw + $short) * Mpdf::SCALE);
					} else {
						$s .= sprintf(' %s %.3F w ', $c, $rbw * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x + $w + $rbw / 2) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE, ($this->x + $w + $rbw / 2) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw + $short) * Mpdf::SCALE);
					}

					if ($this->spanborddet['R']['style'] != 'dotted') {
						$s .= ' Q ';
					}
				}

				$s.= ' Q ';

			} else { // If "border", does not come from WriteFlowingBlock or FinishFlowingBlock

				if ($fill == 1) {
					$op = ($border == 1) ? 'B' : 'f';
				} else {
					$op = 'S';
				}

				$s .= sprintf('%.3F %.3F %.3F %.3F re %s ', $this->x * Mpdf::SCALE, ($this->h - $bg_boxtop) * Mpdf::SCALE, $w * Mpdf::SCALE, -$bg_boxheight * Mpdf::SCALE, $op);
			}
		}

		if (is_string($border)) { // If "border", does not come from WriteFlowingBlock or FinishFlowingBlock

			$x = $this->x;
			$y = $this->y;

			if (is_int(strpos($border, 'L'))) {
				$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', $x * Mpdf::SCALE, ($this->h - $bord_boxtop) * Mpdf::SCALE, $x * Mpdf::SCALE, ($this->h - ($bord_boxbottom)) * Mpdf::SCALE);
			}

			if (is_int(strpos($border, 'T'))) {
				$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', $x * Mpdf::SCALE, ($this->h - $bord_boxtop) * Mpdf::SCALE, ($x + $w) * Mpdf::SCALE, ($this->h - $bord_boxtop) * Mpdf::SCALE);
			}

			if (is_int(strpos($border, 'R'))) {
				$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($x + $w) * Mpdf::SCALE, ($this->h - $bord_boxtop) * Mpdf::SCALE, ($x + $w) * Mpdf::SCALE, ($this->h - ($bord_boxbottom)) * Mpdf::SCALE);
			}

			if (is_int(strpos($border, 'B'))) {
				$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', $x * Mpdf::SCALE, ($this->h - ($bord_boxbottom)) * Mpdf::SCALE, ($x + $w) * Mpdf::SCALE, ($this->h - ($bord_boxbottom)) * Mpdf::SCALE);
			}
		}

		if ($txt != '') {

			if ($exactWidth) {
				$stringWidth = $w;
			} else {
				$stringWidth = $this->GetStringWidth($txt, true, $OTLdata, $textvar) + ( $this->charspacing * mb_strlen($txt, $this->mb_enc) / Mpdf::SCALE ) + ( $this->ws * mb_substr_count($txt, ' ', $this->mb_enc) / Mpdf::SCALE );
			}

			// Set x OFFSET FOR PRINTING
			if ($align == 'R') {
				$dx = $w - $this->cMarginR - $stringWidth - $lcpaddingR;
			} elseif ($align == 'C') {
				$dx = (($w - $stringWidth ) / 2);
			} elseif ($align == 'L' or $align == 'J') {
				$dx = $this->cMarginL + $lcpaddingL;
			} else {
				$dx = 0;
			}

			if ($this->ColorFlag) {
				$s .='q ' . $this->TextColor . ' ';
			}

			// OUTLINE
			if (isset($this->textparam['outline-s']) && $this->textparam['outline-s'] && !($textvar & TextVars::FC_SMALLCAPS)) { // mPDF 5.7.1
				$s .=' ' . sprintf('%.3F w', $this->LineWidth * Mpdf::SCALE) . ' ';
				$s .=" $this->DrawColor ";
				$s .=" 2 Tr ";
			} elseif ($this->falseBoldWeight && strpos($this->ReqFontStyle, "B") !== false && strpos($this->FontStyle, "B") === false && !($textvar & TextVars::FC_SMALLCAPS)) { // can't use together with OUTLINE or Small Caps	// mPDF 5.7.1	??? why not with SmallCaps ???
				$s .= ' 2 Tr 1 J 1 j ';
				$s .= ' ' . sprintf('%.3F w', ($this->FontSize / 130) * Mpdf::SCALE * $this->falseBoldWeight) . ' ';
				$tc = strtoupper($this->TextColor); // change 0 0 0 rg to 0 0 0 RG
				if ($this->FillColor != $tc) {
					$s .= ' ' . $tc . ' ';
				}  // stroke (outline) = same colour as text(fill)
			} else {
				$s .=" 0 Tr ";
			}

			if (strpos($this->ReqFontStyle, "I") !== false && strpos($this->FontStyle, "I") === false) { // Artificial italic
				$aix = '1 0 0.261799 1 %.3F %.3F Tm ';
			} else {
				$aix = '%.3F %.3F Td ';
			}

			$px = ($this->x + $dx) * Mpdf::SCALE;
			$py = ($this->h - ($this->y + $glyphYorigin - $baseline_shift)) * Mpdf::SCALE;

			// THE TEXT
			$txt2 = $txt;
			$sub = '';
			$this->CurrentFont['used'] = true;

			/*             * ************** SIMILAR TO Text() ************************ */

			// IF corefonts AND NOT SmCaps AND NOT Kerning
			// Just output text; charspacing and wordspacing already set by charspacing (Tc) and ws (Tw)
			if ($this->usingCoreFont && !($textvar & TextVars::FC_SMALLCAPS) && !($textvar & TextVars::FC_KERNING)) {
				$txt2 = $this->writer->escape($txt2);
				$sub .= sprintf('BT ' . $aix . ' (%s) Tj ET', $px, $py, $txt2);
			} // IF NOT corefonts AND NO wordspacing AND NOT SIP/SMP AND NOT SmCaps AND NOT Kerning AND NOT OTL
			// Just output text
			elseif (!$this->usingCoreFont && !$this->ws && !($textvar & TextVars::FC_SMALLCAPS) && !($textvar & TextVars::FC_KERNING) && !(isset($this->CurrentFont['useOTL']) && ($this->CurrentFont['useOTL'] & 0xFF) && !empty($OTLdata['GPOSinfo']))) {
				// IF SIP/SMP
				if ((isset($this->CurrentFont['sip']) && $this->CurrentFont['sip']) || (isset($this->CurrentFont['smp']) && $this->CurrentFont['smp'])) {
					$txt2 = $this->UTF8toSubset($txt2);
					$sub .=sprintf('BT ' . $aix . ' %s Tj ET', $px, $py, $txt2);
				} // NOT SIP/SMP
				else {
					$txt2 = $this->writer->utf8ToUtf16BigEndian($txt2, false);
					$txt2 = $this->writer->escape($txt2);
					$sub .=sprintf('BT ' . $aix . ' (%s) Tj ET', $px, $py, $txt2);
				}
			} // IF NOT corefonts AND IS wordspacing AND NOT SIP AND NOT SmCaps AND NOT Kerning AND NOT OTL
			// Output text word by word with an adjustment to the intercharacter spacing for SPACEs to form word spacing
			// IF multibyte - Tw has no effect - need to do word spacing using an adjustment before each space
			elseif (!$this->usingCoreFont && $this->ws && !((isset($this->CurrentFont['sip']) && $this->CurrentFont['sip']) || (isset($this->CurrentFont['smp']) && $this->CurrentFont['smp'])) && !($textvar & TextVars::FC_SMALLCAPS) && !($textvar & TextVars::FC_KERNING) && !(isset($this->CurrentFont['useOTL']) && ($this->CurrentFont['useOTL'] & 0xFF) && (!empty($OTLdata['GPOSinfo']) || (strpos($OTLdata['group'], 'M') !== false && $this->charspacing)) )) {
				$space = " ";
				$space = $this->writer->utf8ToUtf16BigEndian($space, false);
				$space = $this->writer->escape($space);
				$sub .=sprintf('BT ' . $aix . ' %.3F Tc [', $px, $py, $this->charspacing);
				$t = explode(' ', $txt2);
				$numt = count($t);
				for ($i = 0; $i < $numt; $i++) {
					$tx = $t[$i];
					$tx = $this->writer->utf8ToUtf16BigEndian($tx, false);
					$tx = $this->writer->escape($tx);
					$sub .=sprintf('(%s) ', $tx);
					if (($i + 1) < $numt) {
						$adj = -($this->ws) * 1000 / $this->FontSizePt;
						$sub .=sprintf('%d(%s) ', $adj, $space);
					}
				}
				$sub .='] TJ ';
				$sub .=' ET';
			} // ELSE (IF SmCaps || Kerning || OTL) [corefonts or not corefonts; SIP or SMP or BMP]
			else {
				$sub = $this->applyGPOSpdf($txt, $aix, $px, $py, $OTLdata, $textvar);
			}

			/** ************** END SIMILAR TO Text() ************************ */

			if ($this->shrin_k > 1) {
				$shrin_k = $this->shrin_k;
			} else {
				$shrin_k = 1;
			}

			// UNDERLINE
			if ($textvar & TextVars::FD_UNDERLINE) { // mPDF 5.7.1	// mPDF 6

				// mPDF 5.7.3  inline text-decoration parameters

				$c = isset($this->textparam['u-decoration']['color']) ? $this->textparam['u-decoration']['color'] : '';
				if ($this->FillColor != $c) {
					$sub .= ' ' . $c . ' ';
				}

				// mPDF 5.7.3  inline text-decoration parameters
				$decorationfontkey = isset($this->textparam['u-decoration']['fontkey']) ? $this->textparam['u-decoration']['fontkey'] : '';
				$decorationfontsize = isset($this->textparam['u-decoration']['fontsize']) ? $this->textparam['u-decoration']['fontsize'] / $shrin_k : 0;

				if (isset($this->fonts[$decorationfontkey]['ut']) && $this->fonts[$decorationfontkey]['ut']) {
					$ut = $this->fonts[$decorationfontkey]['ut'] / 1000 * $decorationfontsize;
				} else {
					$ut = 60 / 1000 * $decorationfontsize;
				}

				if (isset($this->fonts[$decorationfontkey]['up']) && $this->fonts[$decorationfontkey]['up']) {
					$up = $this->fonts[$decorationfontkey]['up'];
				} else {
					$up = -100;
				}

				$adjusty = (-$up / 1000 * $decorationfontsize) + $ut / 2;
				$ubaseline = isset($this->textparam['u-decoration']['baseline'])
					? $glyphYorigin - $this->textparam['u-decoration']['baseline'] / $shrin_k
					: $glyphYorigin;

				$olw = $this->LineWidth;

				$sub .= ' ' . (sprintf(' %.3F w 0 j 0 J ', $ut * Mpdf::SCALE));
				$sub .= ' ' . $this->_dounderline($this->x + $dx, $this->y + $ubaseline + $adjusty, $txt, $OTLdata, $textvar);
				$sub .= ' ' . (sprintf(' %.3F w 2 j 2 J ', $olw * Mpdf::SCALE));

				if ($this->FillColor != $c) {
					$sub .= ' ' . $this->FillColor . ' ';
				}
			}

			// STRIKETHROUGH
			if ($textvar & TextVars::FD_LINETHROUGH) { // mPDF 5.7.1	// mPDF 6

				// mPDF 5.7.3  inline text-decoration parameters
				$c = $this->textparam['s-decoration']['color'];

				if ($this->FillColor != $c) {
					$sub .= ' ' . $c . ' ';
				}

				// mPDF 5.7.3  inline text-decoration parameters
				$decorationfontkey = $this->textparam['s-decoration']['fontkey'];
				$decorationfontsize = $this->textparam['s-decoration']['fontsize'] / $shrin_k;

				// Use yStrikeoutSize from OS/2 if available
				if (isset($this->fonts[$decorationfontkey]['strs']) && $this->fonts[$decorationfontkey]['strs']) {
					$ut = $this->fonts[$decorationfontkey]['strs'] / 1000 * $decorationfontsize;
				} // else use underlineThickness from post if available
				elseif (isset($this->fonts[$decorationfontkey]['ut']) && $this->fonts[$decorationfontkey]['ut']) {
					$ut = $this->fonts[$decorationfontkey]['ut'] / 1000 * $decorationfontsize;
				} else {
					$ut = 50 / 1000 * $decorationfontsize;
				}

				// Use yStrikeoutPosition from OS/2 if available
				if (isset($this->fonts[$decorationfontkey]['strp']) && $this->fonts[$decorationfontkey]['strp']) {
					$up = $this->fonts[$decorationfontkey]['strp'];
					$adjusty = (-$up / 1000 * $decorationfontsize);
				} // else use a fraction ($this->baselineS) of CapHeight
				else {
					if (isset($this->fonts[$decorationfontkey]['desc']['CapHeight']) && $this->fonts[$decorationfontkey]['desc']['CapHeight']) {
						$ch = $this->fonts[$decorationfontkey]['desc']['CapHeight'];
					} else {
						$ch = 700;
					}
					$adjusty = (-$ch / 1000 * $decorationfontsize) * $this->baselineS;
				}

				$sbaseline = $glyphYorigin - $this->textparam['s-decoration']['baseline'] / $shrin_k;

				$olw = $this->LineWidth;

				$sub .=' ' . (sprintf(' %.3F w 0 j 0 J ', $ut * Mpdf::SCALE));
				$sub .=' ' . $this->_dounderline($this->x + $dx, $this->y + $sbaseline + $adjusty, $txt, $OTLdata, $textvar);
				$sub .=' ' . (sprintf(' %.3F w 2 j 2 J ', $olw * Mpdf::SCALE));

				if ($this->FillColor != $c) {
					$sub .= ' ' . $this->FillColor . ' ';
				}
			}

			// mPDF 5.7.3  inline text-decoration parameters
			// OVERLINE
			if ($textvar & TextVars::FD_OVERLINE) { // mPDF 5.7.1	// mPDF 6
				// mPDF 5.7.3  inline text-decoration parameters
				$c = $this->textparam['o-decoration']['color'];
				if ($this->FillColor != $c) {
					$sub .= ' ' . $c . ' ';
				}

				// mPDF 5.7.3  inline text-decoration parameters
				$decorationfontkey = (int) (((float) $this->textparam['o-decoration']['fontkey']) / $shrin_k);
				$decorationfontsize = $this->textparam['o-decoration']['fontsize'];

				if (isset($this->fonts[$decorationfontkey]['ut']) && $this->fonts[$decorationfontkey]['ut']) {
					$ut = $this->fonts[$decorationfontkey]['ut'] / 1000 * $decorationfontsize;
				} else {
					$ut = 60 / 1000 * $decorationfontsize;
				}
				if (isset($this->fonts[$decorationfontkey]['desc']['CapHeight']) && $this->fonts[$decorationfontkey]['desc']['CapHeight']) {
					$ch = $this->fonts[$decorationfontkey]['desc']['CapHeight'];
				} else {
					$ch = 700;
				}
				$adjusty = (-$ch / 1000 * $decorationfontsize) * $this->baselineO;
				$obaseline = $glyphYorigin - $this->textparam['o-decoration']['baseline'] / $shrin_k;
				$olw = $this->LineWidth;
				$sub .=' ' . (sprintf(' %.3F w 0 j 0 J ', $ut * Mpdf::SCALE));
				$sub .=' ' . $this->_dounderline($this->x + $dx, $this->y + $obaseline + $adjusty, $txt, $OTLdata, $textvar);
				$sub .=' ' . (sprintf(' %.3F w 2 j 2 J ', $olw * Mpdf::SCALE));
				if ($this->FillColor != $c) {
					$sub .= ' ' . $this->FillColor . ' ';
				}
			}

			// TEXT SHADOW
			if ($this->textshadow) {  // First to process is last in CSS comma separated shadows
				foreach ($this->textshadow as $ts) {
					$s .= ' q ';
					$s .= $this->SetTColor($ts['col'], true) . "\n";
					if ($ts['col'][0] == 5 && ord($ts['col'][4]) < 100) { // RGBa
						$s .= $this->SetAlpha(ord($ts['col'][4]) / 100, 'Normal', true, 'F') . "\n";
					} elseif ($ts['col'][0] == 6 && ord($ts['col'][5]) < 100) { // CMYKa
						$s .= $this->SetAlpha(ord($ts['col'][5]) / 100, 'Normal', true, 'F') . "\n";
					} elseif ($ts['col'][0] == 1 && $ts['col'][2] == 1 && ord($ts['col'][3]) < 100) { // Gray
						$s .= $this->SetAlpha(ord($ts['col'][3]) / 100, 'Normal', true, 'F') . "\n";
					}
					$s .= sprintf(' 1 0 0 1 %.4F %.4F cm', $ts['x'] * Mpdf::SCALE, -$ts['y'] * Mpdf::SCALE) . "\n";
					$s .= $sub;
					$s .= ' Q ';
				}
			}

			$s .= $sub;

			// COLOR
			if ($this->ColorFlag) {
				$s .=' Q';
			}

			// LINK
			if ($link != '') {
				$this->Link($this->x, $boxtop, $w, $boxheight, $link);
			}
		}
		if ($s) {
			$this->writer->write($s);
		}

		// WORD SPACING
		if ($this->ws && !$this->usingCoreFont) {
			$this->writer->write(sprintf('BT %.3F Tc ET', $this->charspacing));
		}
		$this->lasth = $h;
		if (strpos($txt, "\n") !== false) {
			$ln = 1; // cell recognizes \n from <BR> tag
		}
		if ($ln > 0) {
			// Go to next line
			$this->y += $h;
			if ($ln == 1) {
				// Move to next line
				if ($currentx != 0) {
					$this->x = $currentx;
				} else {
					$this->x = $this->lMargin;
				}
			}
		} else {
			$this->x+=$w;
		}
	}

	function applyGPOSpdf($txt, $aix, $x, $y, $OTLdata, $textvar = 0)
	{
		// Generate PDF string
		// ==============================
		if ((isset($this->CurrentFont['sip']) && $this->CurrentFont['sip']) || (isset($this->CurrentFont['smp']) && $this->CurrentFont['smp'])) {
			$sipset = true;
		} else {
			$sipset = false;
		}

		if ($textvar & TextVars::FC_SMALLCAPS) {
			$smcaps = true;
		} // IF SmallCaps using transformation, NOT OTL
		else {
			$smcaps = false;
		}

		if ($sipset) {
			$fontid = $last_fontid = $original_fontid = $this->CurrentFont['subsetfontids'][0];
		} else {
			$fontid = $last_fontid = $original_fontid = $this->CurrentFont['i'];
		}
		$SmallCapsON = false;  // state: uppercase/not
		$lastSmallCapsON = false; // state: uppercase/not
		$last_fontsize = $fontsize = $this->FontSizePt;
		$last_fontstretch = $fontstretch = 100;
		$groupBreak = false;

		$unicode = $this->UTF8StringToArray($txt);

		$GPOSinfo = (isset($OTLdata['GPOSinfo']) ? $OTLdata['GPOSinfo'] : []);
		$charspacing = ($this->charspacing * 1000 / $this->FontSizePt);
		$wordspacing = ($this->ws * 1000 / $this->FontSizePt);

		$XshiftBefore = 0;
		$XshiftAfter = 0;
		$lastYPlacement = 0;

		if ($sipset) {
			// mPDF 6  DELETED ********
			// 	$txt= preg_replace('/'.preg_quote($this->aliasNbPg,'/').'/', chr(7), $txt);	// ? Need to adjust OTL info
			// 	$txt= preg_replace('/'.preg_quote($this->aliasNbPgGp,'/').'/', chr(8), $txt);	// ? Need to adjust OTL info
			$tj = '<';
		} else {
			$tj = '(';
		}

		for ($i = 0; $i < count($unicode); $i++) {
			$c = $unicode[$i];
			$tx = '';
			$XshiftBefore = $XshiftAfter;
			$XshiftAfter = 0;
			$YPlacement = 0;
			$groupBreak = false;
			$kashida = 0;
			if (!empty($OTLdata)) {
				// YPlacement from GPOS
				if (isset($GPOSinfo[$i]['YPlacement']) && $GPOSinfo[$i]['YPlacement']) {
					$YPlacement = $GPOSinfo[$i]['YPlacement'] * $this->FontSizePt / $this->CurrentFont['unitsPerEm'];
					$groupBreak = true;
				}
				// XPlacement from GPOS
				if (isset($GPOSinfo[$i]['XPlacement']) && $GPOSinfo[$i]['XPlacement']) {
					if (!isset($GPOSinfo[$i]['wDir']) || $GPOSinfo[$i]['wDir'] != 'RTL') {
						if (isset($GPOSinfo[$i]['BaseWidth'])) {
							$GPOSinfo[$i]['XPlacement'] -= $GPOSinfo[$i]['BaseWidth'];
						}
					}

					// Convert to PDF Text space (thousandths of a unit );
					$XshiftBefore += $GPOSinfo[$i]['XPlacement'] * 1000 / $this->CurrentFont['unitsPerEm'];
					$XshiftAfter += -$GPOSinfo[$i]['XPlacement'] * 1000 / $this->CurrentFont['unitsPerEm'];
				}

				// Kashida from GPOS
				// Kashida is set as an absolute length value, but to adjust text needs to be converted to
				// font-related size
				if (isset($GPOSinfo[$i]['kashida_space']) && $GPOSinfo[$i]['kashida_space']) {
					$kashida = $GPOSinfo[$i]['kashida_space'];
				}

				if ($c == 32) { // word spacing
					$XshiftAfter += $wordspacing;
				}

				if (substr($OTLdata['group'], ($i + 1), 1) != 'M') { // Don't add inter-character spacing before Marks
					$XshiftAfter += $charspacing;
				}

				// ...applyGPOSpdf...
				// XAdvance from GPOS - Convert to PDF Text space (thousandths of a unit );
				if (((isset($GPOSinfo[$i]['wDir']) && $GPOSinfo[$i]['wDir'] != 'RTL') || !isset($GPOSinfo[$i]['wDir'])) && isset($GPOSinfo[$i]['XAdvanceL']) && $GPOSinfo[$i]['XAdvanceL']) {
					$XshiftAfter += $GPOSinfo[$i]['XAdvanceL'] * 1000 / $this->CurrentFont['unitsPerEm'];
				} elseif (isset($GPOSinfo[$i]['wDir']) && $GPOSinfo[$i]['wDir'] == 'RTL' && isset($GPOSinfo[$i]['XAdvanceR']) && $GPOSinfo[$i]['XAdvanceR']) {
					$XshiftAfter += $GPOSinfo[$i]['XAdvanceR'] * 1000 / $this->CurrentFont['unitsPerEm'];
				}
			} // Character & Word spacing - if NOT OTL
			else {
				$XshiftAfter += $charspacing;
				if ($c == 32) {
					$XshiftAfter += $wordspacing;
				}
			}

			// IF Kerning done using pairs rather than OTL
			if ($textvar & TextVars::FC_KERNING) {
				if ($i > 0 && isset($this->CurrentFont['kerninfo'][$unicode[($i - 1)]][$unicode[$i]])) {
					$XshiftBefore += $this->CurrentFont['kerninfo'][$unicode[($i - 1)]][$unicode[$i]];
				}
			}

			if ($YPlacement != $lastYPlacement) {
				$groupBreak = true;
			}

			if ($XshiftBefore) {  // +ve value in PDF moves to the left
				// If Fontstretch is ongoing, need to adjust X adjustments because these will be stretched out.
				$XshiftBefore *= 100 / $last_fontstretch;
				if ($sipset) {
					$tj .= sprintf('>%d<', (-$XshiftBefore));
				} else {
					$tj .= sprintf(')%d(', (-$XshiftBefore));
				}
			}

			// Small-Caps
			if ($smcaps) {
				if (isset($this->upperCase[$c])) {
					$c = $this->upperCase[$c];
					// $this->CurrentFont['subset'][$this->upperCase[$c]] = $this->upperCase[$c];	// add the CAP to subset
					$SmallCapsON = true;
					// For $sipset
					if (!$lastSmallCapsON) {   // Turn ON SmallCaps
						$groupBreak = true;
						$fontstretch = $this->smCapsStretch;
						$fontsize = $this->FontSizePt * $this->smCapsScale;
					}
				} else {
					$SmallCapsON = false;
					if ($lastSmallCapsON) {  // Turn OFF SmallCaps
						$groupBreak = true;
						$fontstretch = 100;
						$fontsize = $this->FontSizePt;
					}
				}
			}

			// Prepare Text and Select Font ID
			if ($sipset) {
				// mPDF 6  DELETED ********
				// if ($c == 7 || $c == 8) {
				// if ($original_fontid != $last_fontid) {
				// 	$groupBreak = true;
				// 	$fontid = $original_fontid;
				// }
				// if ($c == 7) { $tj .= $this->aliasNbPgHex; }
				// else { $tj .= $this->aliasNbPgGpHex; }
				// continue;
				// }
				for ($j = 0; $j < 99; $j++) {
					$init = array_search($c, $this->CurrentFont['subsets'][$j]);
					if ($init !== false) {
						if ($this->CurrentFont['subsetfontids'][$j] != $last_fontid) {
							$groupBreak = true;
							$fontid = $this->CurrentFont['subsetfontids'][$j];
						}
						$tx = sprintf("%02s", strtoupper(dechex($init)));
						break;
					} elseif (count($this->CurrentFont['subsets'][$j]) < 255) {
						$n = count($this->CurrentFont['subsets'][$j]);
						$this->CurrentFont['subsets'][$j][$n] = $c;
						if ($this->CurrentFont['subsetfontids'][$j] != $last_fontid) {
							$groupBreak = true;
							$fontid = $this->CurrentFont['subsetfontids'][$j];
						}
						$tx = sprintf("%02s", strtoupper(dechex($n)));
						break;
					} elseif (!isset($this->CurrentFont['subsets'][($j + 1)])) {
						$this->CurrentFont['subsets'][($j + 1)] = [0 => 0];
						$this->CurrentFont['subsetfontids'][($j + 1)] = count($this->fonts) + $this->extraFontSubsets + 1;
						$this->extraFontSubsets++;
					}
				}
			} else {
				$tx = UtfString::code2utf($c);
				if ($this->usingCoreFont) {
					$tx = utf8_decode($tx);
				} else {
					$tx = $this->writer->utf8ToUtf16BigEndian($tx, false);
				}
				$tx = $this->writer->escape($tx);
			}

			// If any settings require a new Text Group
			if ($groupBreak || $fontstretch != $last_fontstretch) {
				if ($sipset) {
					$tj .= '>] TJ ';
				} else {
					$tj .= ')] TJ ';
				}
				if ($fontid != $last_fontid || $fontsize != $last_fontsize) {
					$tj .= sprintf(' /F%d %.3F Tf ', $fontid, $fontsize);
				}
				if ($fontstretch != $last_fontstretch) {
					$tj .= sprintf('%d Tz ', $fontstretch);
				}
				if ($YPlacement != $lastYPlacement) {
					$tj .= sprintf('%.3F Ts ', $YPlacement);
				}
				if ($sipset) {
					$tj .= '[<';
				} else {
					$tj .= '[(';
				}
			}

			// Output the code for the txt character
			$tj .= $tx;
			$lastSmallCapsON = $SmallCapsON;
			$last_fontid = $fontid;
			$last_fontsize = $fontsize;
			$last_fontstretch = $fontstretch;

			// Kashida
			if ($kashida) {
				$c = 0x0640; // add the Tatweel U+0640
				if (isset($this->CurrentFont['subset'])) {
					$this->CurrentFont['subset'][$c] = $c;
				}
				$kashida *= 1000 / $this->FontSizePt;
				$tatw = $this->_getCharWidth($this->CurrentFont['cw'], 0x0640);

				// Get YPlacement from next Base character
				$nextbase = $i + 1;
				while ($OTLdata['group'][$nextbase] != 'C') {
					$nextbase++;
				}
				if (isset($GPOSinfo[$nextbase]) && isset($GPOSinfo[$nextbase]['YPlacement']) && $GPOSinfo[$nextbase]['YPlacement']) {
					$YPlacement = $GPOSinfo[$nextbase]['YPlacement'] * $this->FontSizePt / $this->CurrentFont['unitsPerEm'];
				}

				// Prepare Text and Select Font ID
				if ($sipset) {
					for ($j = 0; $j < 99; $j++) {
						$init = array_search($c, $this->CurrentFont['subsets'][$j]);
						if ($init !== false) {
							if ($this->CurrentFont['subsetfontids'][$j] != $last_fontid) {
								$fontid = $this->CurrentFont['subsetfontids'][$j];
							}
							$tx = sprintf("%02s", strtoupper(dechex($init)));
							break;
						} elseif (count($this->CurrentFont['subsets'][$j]) < 255) {
							$n = count($this->CurrentFont['subsets'][$j]);
							$this->CurrentFont['subsets'][$j][$n] = $c;
							if ($this->CurrentFont['subsetfontids'][$j] != $last_fontid) {
								$fontid = $this->CurrentFont['subsetfontids'][$j];
							}
							$tx = sprintf("%02s", strtoupper(dechex($n)));
							break;
						} elseif (!isset($this->CurrentFont['subsets'][($j + 1)])) {
							$this->CurrentFont['subsets'][($j + 1)] = [0 => 0];
							$this->CurrentFont['subsetfontids'][($j + 1)] = count($this->fonts) + $this->extraFontSubsets + 1;
							$this->extraFontSubsets++;
						}
					}
				} else {
					$tx = UtfString::code2utf($c);
					$tx = $this->writer->utf8ToUtf16BigEndian($tx, false);
					$tx = $this->writer->escape($tx);
				}

				if ($kashida > $tatw) {
					// Insert multiple tatweel characters, repositioning the last one to give correct total length
					$fontstretch = 100;
					$nt = intval($kashida / $tatw);
					$nudgeback = (($nt + 1) * $tatw) - $kashida;
					$optx = str_repeat($tx, $nt);
					if ($sipset) {
						$optx .= sprintf('>%d<', ($nudgeback));
					} else {
						$optx .= sprintf(')%d(', ($nudgeback));
					}
					$optx .= $tx; // #last
				} else {
					// Insert single tatweel character and use fontstretch to get correct length
					$fontstretch = ($kashida / $tatw) * 100;
					$optx = $tx;
				}

				if ($sipset) {
					$tj .= '>] TJ ';
				} else {
					$tj .= ')] TJ ';
				}
				if ($fontid != $last_fontid || $fontsize != $last_fontsize) {
					$tj .= sprintf(' /F%d %.3F Tf ', $fontid, $fontsize);
				}
				if ($fontstretch != $last_fontstretch) {
					$tj .= sprintf('%d Tz ', $fontstretch);
				}
				$tj .= sprintf('%.3F Ts ', $YPlacement);
				if ($sipset) {
					$tj .= '[<';
				} else {
					$tj .= '[(';
				}

				// Output the code for the txt character(s)
				$tj .= $optx;
				$last_fontid = $fontid;
				$last_fontstretch = $fontstretch;
				$fontstretch = 100;
			}

			$lastYPlacement = $YPlacement;
		}


		// Finish up
		if ($sipset) {
			$tj .= '>';
			if ($XshiftAfter) {
				$tj .= sprintf('%d', (-$XshiftAfter));
			}
			if ($last_fontid != $original_fontid) {
				$tj .= '] TJ ';
				$tj .= sprintf(' /F%d %.3F Tf ', $original_fontid, $fontsize);
				$tj .= '[';
			}
			$tj = preg_replace('/([^\\\])<>/', '\\1 ', $tj);
		} else {
			$tj .= ')';
			if ($XshiftAfter) {
				$tj .= sprintf('%d', (-$XshiftAfter));
			}
			if ($last_fontid != $original_fontid) {
				$tj .= '] TJ ';
				$tj .= sprintf(' /F%d %.3F Tf ', $original_fontid, $fontsize);
				$tj .= '[';
			}
			$tj = preg_replace('/([^\\\])\(\)/', '\\1 ', $tj);
		}

		$s = sprintf(' BT ' . $aix . ' 0 Tc 0 Tw [%s] TJ ET ', $x, $y, $tj);

		// echo $s."\n\n"; // exit;

		return $s;
	}

	function _kern($txt, $mode, $aix, $x, $y)
	{
		if ($mode == 'MBTw') { // Multibyte requiring word spacing
			$space = ' ';
			// Convert string to UTF-16BE without BOM
			$space = $this->writer->utf8ToUtf16BigEndian($space, false);
			$space = $this->writer->escape($space);
			$s = sprintf(' BT ' . $aix, $x * Mpdf::SCALE, ($this->h - $y) * Mpdf::SCALE);
			$t = explode(' ', $txt);
			for ($i = 0; $i < count($t); $i++) {
				$tx = $t[$i];

				$tj = '(';
				$unicode = $this->UTF8StringToArray($tx);
				for ($ti = 0; $ti < count($unicode); $ti++) {
					if ($ti > 0 && isset($this->CurrentFont['kerninfo'][$unicode[($ti - 1)]][$unicode[$ti]])) {
						$kern = -$this->CurrentFont['kerninfo'][$unicode[($ti - 1)]][$unicode[$ti]];
						$tj .= sprintf(')%d(', $kern);
					}
					$tc = UtfString::code2utf($unicode[$ti]);
					$tc = $this->writer->utf8ToUtf16BigEndian($tc, false);
					$tj .= $this->writer->escape($tc);
				}
				$tj .= ')';
				$s .= sprintf(' %.3F Tc [%s] TJ', $this->charspacing, $tj);


				if (($i + 1) < count($t)) {
					$s .= sprintf(' %.3F Tc (%s) Tj', $this->ws + $this->charspacing, $space);
				}
			}
			$s .= ' ET ';
		} elseif (!$this->usingCoreFont) {
			$s = '';
			$tj = '(';
			$unicode = $this->UTF8StringToArray($txt);
			for ($i = 0; $i < count($unicode); $i++) {
				if ($i > 0 && isset($this->CurrentFont['kerninfo'][$unicode[($i - 1)]][$unicode[$i]])) {
					$kern = -$this->CurrentFont['kerninfo'][$unicode[($i - 1)]][$unicode[$i]];
					$tj .= sprintf(')%d(', $kern);
				}
				$tx = UtfString::code2utf($unicode[$i]);
				$tx = $this->writer->utf8ToUtf16BigEndian($tx, false);
				$tj .= $this->writer->escape($tx);
			}
			$tj .= ')';
			$s .= sprintf(' BT ' . $aix . ' [%s] TJ ET ', $x * Mpdf::SCALE, ($this->h - $y) * Mpdf::SCALE, $tj);
		} else { // CORE Font
			$s = '';
			$tj = '(';
			$l = strlen($txt);
			for ($i = 0; $i < $l; $i++) {
				if ($i > 0 && isset($this->CurrentFont['kerninfo'][$txt[($i - 1)]][$txt[$i]])) {
					$kern = -$this->CurrentFont['kerninfo'][$txt[($i - 1)]][$txt[$i]];
					$tj .= sprintf(')%d(', $kern);
				}
				$tj .= $this->writer->escape($txt[$i]);
			}
			$tj .= ')';
			$s .= sprintf(' BT ' . $aix . ' [%s] TJ ET ', $x * Mpdf::SCALE, ($this->h - $y) * Mpdf::SCALE, $tj);
		}

		return $s;
	}

	function MultiCell($w, $h, $txt, $border = 0, $align = '', $fill = 0, $link = '', $directionality = 'ltr', $encoded = false, $OTLdata = false, $maxrows = false)
	{
		// maxrows is called from mpdfform->TEXTAREA
		// Parameter (pre-)encoded - When called internally from form::textarea - mb_encoding already done and OTL - but not reverse RTL
		if (!$encoded) {
			$txt = $this->purify_utf8_text($txt);
			if ($this->text_input_as_HTML) {
				$txt = $this->all_entities_to_utf8($txt);
			}
			if ($this->usingCoreFont) {
				$txt = mb_convert_encoding($txt, $this->mb_enc, 'UTF-8');
			}
			if (preg_match("/([" . $this->pregRTLchars . "])/u", $txt)) {
				$this->biDirectional = true;
			} // *OTL*
			/* -- OTL -- */
			$OTLdata = [];
			// Use OTL OpenType Table Layout - GSUB & GPOS
			if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
				$txt = $this->otl->applyOTL($txt, $this->CurrentFont['useOTL']);
				$OTLdata = $this->otl->OTLdata;
			}
			if ($directionality == 'rtl' || $this->biDirectional) {
				if (!isset($OTLdata)) {
					$unicode = $this->UTF8StringToArray($txt, false);
					$is_strong = false;
					$this->getBasicOTLdata($OTLdata, $unicode, $is_strong);
				}
			}
			/* -- END OTL -- */
		}
		if (!$align) {
			$align = $this->defaultAlign;
		}

		// Output text with automatic or explicit line breaks
		$cw = &$this->CurrentFont['cw'];
		if ($w == 0) {
			$w = $this->w - $this->rMargin - $this->x;
		}

		$wmax = ($w - ($this->cMarginL + $this->cMarginR));
		if ($this->usingCoreFont) {
			$s = str_replace("\r", '', $txt);
			$nb = strlen($s);
			while ($nb > 0 and $s[$nb - 1] == "\n") {
				$nb--;
			}
		} else {
			$s = str_replace("\r", '', $txt);
			$nb = mb_strlen($s, $this->mb_enc);
			while ($nb > 0 and mb_substr($s, $nb - 1, 1, $this->mb_enc) == "\n") {
				$nb--;
			}
		}
		$b = 0;
		if ($border) {
			if ($border == 1) {
				$border = 'LTRB';
				$b = 'LRT';
				$b2 = 'LR';
			} else {
				$b2 = '';
				if (is_int(strpos($border, 'L'))) {
					$b2 .= 'L';
				}
				if (is_int(strpos($border, 'R'))) {
					$b2 .= 'R';
				}
				$b = is_int(strpos($border, 'T')) ? $b2 . 'T' : $b2;
			}
		}
		$sep = -1;
		$i = 0;
		$j = 0;
		$l = 0;
		$ns = 0;
		$nl = 1;

		$rows = 0;
		$start_y = $this->y;

		if (!$this->usingCoreFont) {
			$inclCursive = false;
			if (preg_match("/([" . $this->pregCURSchars . "])/u", $s)) {
				$inclCursive = true;
			}
			while ($i < $nb) {
				// Get next character
				$c = mb_substr($s, $i, 1, $this->mb_enc);
				if ($c == "\n") {
					// Explicit line break
					// WORD SPACING
					$this->ResetSpacing();
					$tmp = rtrim(mb_substr($s, $j, $i - $j, $this->mb_enc));
					$tmpOTLdata = false;
					/* -- OTL -- */
					if (isset($OTLdata)) {
						$tmpOTLdata = $this->otl->sliceOTLdata($OTLdata, $j, $i - $j);
						$this->otl->trimOTLdata($tmpOTLdata, false, true);
						$this->magic_reverse_dir($tmp, $directionality, $tmpOTLdata);
					}
					/* -- END OTL -- */
					$this->Cell($w, $h, $tmp, $b, 2, $align, $fill, $link, 0, 0, 0, 'M', 0, false, $tmpOTLdata);
					if ($maxrows != false && isset($this->form) && ($this->y - $start_y) / $h > $maxrows) {
						return false;
					}
					$i++;
					$sep = -1;
					$j = $i;
					$l = 0;
					$ns = 0;
					$nl++;
					if ($border and $nl == 2) {
						$b = $b2;
					}
					continue;
				}
				if ($c == " ") {
					$sep = $i;
					$ls = $l;
					$ns++;
				}

				$l += $this->GetCharWidthNonCore($c);

				if ($l > $wmax) {
					// Automatic line break
					if ($sep == -1) { // Only one word
						if ($i == $j) {
							$i++;
						}
						// WORD SPACING
						$this->ResetSpacing();
						$tmp = rtrim(mb_substr($s, $j, $i - $j, $this->mb_enc));
						$tmpOTLdata = false;
						/* -- OTL -- */
						if (isset($OTLdata)) {
							$tmpOTLdata = $this->otl->sliceOTLdata($OTLdata, $j, $i - $j);
							$this->otl->trimOTLdata($tmpOTLdata, false, true);
							$this->magic_reverse_dir($tmp, $directionality, $tmpOTLdata);
						}
						/* -- END OTL -- */
						$this->Cell($w, $h, $tmp, $b, 2, $align, $fill, $link, 0, 0, 0, 'M', 0, false, $tmpOTLdata);
					} else {
						$tmp = rtrim(mb_substr($s, $j, $sep - $j, $this->mb_enc));
						$tmpOTLdata = false;
						/* -- OTL -- */
						if (isset($OTLdata)) {
							$tmpOTLdata = $this->otl->sliceOTLdata($OTLdata, $j, $sep - $j);
							$this->otl->trimOTLdata($tmpOTLdata, false, true);
						}
						/* -- END OTL -- */
						if ($align == 'J') {
							//////////////////////////////////////////
							// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
							// WORD SPACING UNICODE
							// Change NON_BREAKING SPACE to spaces so they are 'spaced' properly
							$tmp = str_replace(chr(194) . chr(160), chr(32), $tmp);
							$len_ligne = $this->GetStringWidth($tmp, false, $tmpOTLdata);
							$nb_carac = mb_strlen($tmp, $this->mb_enc);
							$nb_spaces = mb_substr_count($tmp, ' ', $this->mb_enc);
							// Take off number of Marks
							// Use GPOS OTL
							if (isset($this->CurrentFont['useOTL']) && ($this->CurrentFont['useOTL'])) {
								if (isset($tmpOTLdata['group']) && $tmpOTLdata['group']) {
									$nb_carac -= substr_count($tmpOTLdata['group'], 'M');
								}
							}

							list($charspacing, $ws, $kashida) = $this->GetJspacing($nb_carac, $nb_spaces, ((($wmax) - $len_ligne) * Mpdf::SCALE), $inclCursive, $tmpOTLdata);
							$this->SetSpacing($charspacing, $ws);
							//////////////////////////////////////////
						}
						if (isset($OTLdata)) {
							$this->magic_reverse_dir($tmp, $directionality, $tmpOTLdata);
						}
						$this->Cell($w, $h, $tmp, $b, 2, $align, $fill, $link, 0, 0, 0, 'M', 0, false, $tmpOTLdata);
						$i = $sep + 1;
					}
					if ($maxrows != false && isset($this->form) && ($this->y - $start_y) / $h > $maxrows) {
						return false;
					}
					$sep = -1;
					$j = $i;
					$l = 0;
					$ns = 0;
					$nl++;
					if ($border and $nl == 2) {
						$b = $b2;
					}
				} else {
					$i++;
				}
			}
			// Last chunk
			// WORD SPACING

			$this->ResetSpacing();
		} else {
			while ($i < $nb) {
				// Get next character
				$c = $s[$i];
				if ($c == "\n") {
					// Explicit line break
					// WORD SPACING
					$this->ResetSpacing();
					$this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill, $link);
					if ($maxrows != false && isset($this->form) && ($this->y - $start_y) / $h > $maxrows) {
						return false;
					}
					$i++;
					$sep = -1;
					$j = $i;
					$l = 0;
					$ns = 0;
					$nl++;
					if ($border and $nl == 2) {
						$b = $b2;
					}
					continue;
				}
				if ($c == " ") {
					$sep = $i;
					$ls = $l;
					$ns++;
				}

				$l += $this->GetCharWidthCore($c);
				if ($l > $wmax) {
					// Automatic line break
					if ($sep == -1) {
						if ($i == $j) {
							$i++;
						}
						// WORD SPACING
						$this->ResetSpacing();
						$this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill, $link);
					} else {
						if ($align == 'J') {
							$tmp = rtrim(substr($s, $j, $sep - $j));
							//////////////////////////////////////////
							// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
							// WORD SPACING NON_UNICODE/CJK
							// Change NON_BREAKING SPACE to spaces so they are 'spaced' properly
							$tmp = str_replace(chr(160), chr(32), $tmp);
							$len_ligne = $this->GetStringWidth($tmp);
							$nb_carac = strlen($tmp);
							$nb_spaces = substr_count($tmp, ' ');
							$tmpOTLdata = [];
							list($charspacing, $ws, $kashida) = $this->GetJspacing($nb_carac, $nb_spaces, ((($wmax) - $len_ligne) * Mpdf::SCALE), false, $tmpOTLdata);
							$this->SetSpacing($charspacing, $ws);
							//////////////////////////////////////////
						}
						$this->Cell($w, $h, substr($s, $j, $sep - $j), $b, 2, $align, $fill, $link);
						$i = $sep + 1;
					}
					if ($maxrows != false && isset($this->form) && ($this->y - $start_y) / $h > $maxrows) {
						return false;
					}
					$sep = -1;
					$j = $i;
					$l = 0;
					$ns = 0;
					$nl++;
					if ($border and $nl == 2) {
						$b = $b2;
					}
				} else {
					$i++;
				}
			}
			// Last chunk
			// WORD SPACING

			$this->ResetSpacing();
		}
		// Last chunk
		if ($border and is_int(strpos($border, 'B'))) {
			$b .= 'B';
		}
		if (!$this->usingCoreFont) {
			$tmp = rtrim(mb_substr($s, $j, $i - $j, $this->mb_enc));
			$tmpOTLdata = false;
			/* -- OTL -- */
			if (isset($OTLdata)) {
				$tmpOTLdata = $this->otl->sliceOTLdata($OTLdata, $j, $i - $j);
				$this->otl->trimOTLdata($tmpOTLdata, false, true);
				$this->magic_reverse_dir($tmp, $directionality, $tmpOTLdata);
			}
			/* -- END OTL -- */
			$this->Cell($w, $h, $tmp, $b, 2, $align, $fill, $link, 0, 0, 0, 'M', 0, false, $tmpOTLdata);
		} else {
			$this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill, $link);
		}
		$this->x = $this->lMargin;
	}

	/* -- DIRECTW -- */

	function Write($h, $txt, $currentx = 0, $link = '', $directionality = 'ltr', $align = '', $fill = 0)
	{
		if (empty($this->directWrite)) {
			$this->directWrite = new DirectWrite($this, $this->otl, $this->sizeConverter, $this->colorConverter);
		}

		$this->directWrite->Write($h, $txt, $currentx, $link, $directionality, $align, $fill);
	}

	/* -- END DIRECTW -- */


	/* -- HTML-CSS -- */

	function saveInlineProperties()
	{
		$saved = [];
		$saved['family'] = $this->FontFamily;
		$saved['style'] = $this->FontStyle;
		$saved['sizePt'] = $this->FontSizePt;
		$saved['size'] = $this->FontSize;
		$saved['HREF'] = $this->HREF;
		$saved['textvar'] = $this->textvar; // mPDF 5.7.1
		$saved['OTLtags'] = $this->OTLtags; // mPDF 5.7.1
		$saved['textshadow'] = $this->textshadow;
		$saved['linewidth'] = $this->LineWidth;
		$saved['drawcolor'] = $this->DrawColor;
		$saved['textparam'] = $this->textparam;
		$saved['lSpacingCSS'] = $this->lSpacingCSS;
		$saved['wSpacingCSS'] = $this->wSpacingCSS;
		$saved['I'] = $this->I;
		$saved['B'] = $this->B;
		$saved['colorarray'] = $this->colorarray;
		$saved['bgcolorarray'] = $this->spanbgcolorarray;
		$saved['border'] = $this->spanborddet;
		$saved['color'] = $this->TextColor;
		$saved['bgcolor'] = $this->FillColor;
		$saved['lang'] = $this->currentLang;
		$saved['fontLanguageOverride'] = $this->fontLanguageOverride; // mPDF 5.7.1
		$saved['display_off'] = $this->inlineDisplayOff;

		return $saved;
	}

	function restoreInlineProperties(&$saved)
	{
		$FontFamily = $saved['family'];
		$this->FontStyle = $saved['style'];
		$this->FontSizePt = $saved['sizePt'];
		$this->FontSize = $saved['size'];

		$this->currentLang = $saved['lang'];
		$this->fontLanguageOverride = $saved['fontLanguageOverride']; // mPDF 5.7.1

		$this->ColorFlag = ($this->FillColor != $this->TextColor); // Restore ColorFlag as well

		$this->HREF = $saved['HREF'];
		$this->textvar = $saved['textvar']; // mPDF 5.7.1
		$this->OTLtags = $saved['OTLtags']; // mPDF 5.7.1
		$this->textshadow = $saved['textshadow'];
		$this->LineWidth = $saved['linewidth'];
		$this->DrawColor = $saved['drawcolor'];
		$this->textparam = $saved['textparam'];
		$this->inlineDisplayOff = $saved['display_off'];

		$this->lSpacingCSS = $saved['lSpacingCSS'];
		if (($this->lSpacingCSS || $this->lSpacingCSS === '0') && strtoupper($this->lSpacingCSS) != 'NORMAL') {
			$this->fixedlSpacing = $this->sizeConverter->convert($this->lSpacingCSS, $this->FontSize);
		} else {
			$this->fixedlSpacing = false;
		}
		$this->wSpacingCSS = $saved['wSpacingCSS'];
		if ($this->wSpacingCSS && strtoupper($this->wSpacingCSS) != 'NORMAL') {
			$this->minwSpacing = $this->sizeConverter->convert($this->wSpacingCSS, $this->FontSize);
		} else {
			$this->minwSpacing = 0;
		}

		$this->SetFont($FontFamily, $saved['style'], $saved['sizePt'], false);

		$this->currentfontstyle = $saved['style'];
		$this->currentfontsize = $saved['sizePt'];
		$this->SetStylesArray(['B' => $saved['B'], 'I' => $saved['I']]); // mPDF 5.7.1

		$this->TextColor = $saved['color'];
		$this->FillColor = $saved['bgcolor'];
		$this->colorarray = $saved['colorarray'];
		$cor = $saved['colorarray'];
		if ($cor) {
			$this->SetTColor($cor);
		}
		$this->spanbgcolorarray = $saved['bgcolorarray'];
		$cor = $saved['bgcolorarray'];
		if ($cor) {
			$this->SetFColor($cor);
		}
		$this->spanborddet = $saved['border'];
	}

	// Used when ColActive for tables - updated to return first block with background fill OR borders
	function GetFirstBlockFill()
	{
		// Returns the first blocklevel that uses a bgcolor fill
		$startfill = 0;
		for ($i = 1; $i <= $this->blklvl; $i++) {
			if ($this->blk[$i]['bgcolor'] || $this->blk[$i]['border_left']['w'] || $this->blk[$i]['border_right']['w'] || $this->blk[$i]['border_top']['w'] || $this->blk[$i]['border_bottom']['w']) {
				$startfill = $i;
				break;
			}
		}
		return $startfill;
	}

	// -------------------------FLOWING BLOCK------------------------------------//
	// The following functions were originally written by Damon Kohler           //
	// --------------------------------------------------------------------------//

	function saveFont()
	{
		$saved = [];
		$saved['family'] = $this->FontFamily;
		$saved['style'] = $this->FontStyle;
		$saved['sizePt'] = $this->FontSizePt;
		$saved['size'] = $this->FontSize;
		$saved['curr'] = &$this->CurrentFont;
		$saved['lang'] = $this->currentLang; // mPDF 6
		$saved['color'] = $this->TextColor;
		$saved['spanbgcolor'] = $this->spanbgcolor;
		$saved['spanbgcolorarray'] = $this->spanbgcolorarray;
		$saved['bord'] = $this->spanborder;
		$saved['border'] = $this->spanborddet;
		$saved['HREF'] = $this->HREF;
		$saved['textvar'] = $this->textvar; // mPDF 5.7.1
		$saved['textshadow'] = $this->textshadow;
		$saved['linewidth'] = $this->LineWidth;
		$saved['drawcolor'] = $this->DrawColor;
		$saved['textparam'] = $this->textparam;
		$saved['ReqFontStyle'] = $this->ReqFontStyle;
		$saved['fixedlSpacing'] = $this->fixedlSpacing;
		$saved['minwSpacing'] = $this->minwSpacing;
		return $saved;
	}

	function restoreFont(&$saved, $write = true)
	{
		if (!isset($saved) || empty($saved)) {
			return;
		}

		$this->FontFamily = $saved['family'];
		$this->FontStyle = $saved['style'];
		$this->FontSizePt = $saved['sizePt'];
		$this->FontSize = $saved['size'];
		$this->CurrentFont = &$saved['curr'];
		$this->currentLang = $saved['lang']; // mPDF 6
		$this->TextColor = $saved['color'];
		$this->spanbgcolor = $saved['spanbgcolor'];
		$this->spanbgcolorarray = $saved['spanbgcolorarray'];
		$this->spanborder = $saved['bord'];
		$this->spanborddet = $saved['border'];
		$this->ColorFlag = ($this->FillColor != $this->TextColor); // Restore ColorFlag as well
		$this->HREF = $saved['HREF'];
		$this->fixedlSpacing = $saved['fixedlSpacing'];
		$this->minwSpacing = $saved['minwSpacing'];
		$this->textvar = $saved['textvar'];  // mPDF 5.7.1
		$this->textshadow = $saved['textshadow'];
		$this->LineWidth = $saved['linewidth'];
		$this->DrawColor = $saved['drawcolor'];
		$this->textparam = $saved['textparam'];
		if ($write) {
			$this->SetFont($saved['family'], $saved['style'], $saved['sizePt'], true, true); // force output
			$fontout = (sprintf('BT /F%d %.3F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
			if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['Font']) && $this->pageoutput[$this->page]['Font'] != $fontout) || !isset($this->pageoutput[$this->page]['Font']))) {
				$this->writer->write($fontout);
			}
			$this->pageoutput[$this->page]['Font'] = $fontout;
		} else {
			$this->SetFont($saved['family'], $saved['style'], $saved['sizePt'], false);
		}
		$this->ReqFontStyle = $saved['ReqFontStyle'];
	}

	function newFlowingBlock($w, $h, $a = '', $is_table = false, $blockstate = 0, $newblock = true, $blockdir = 'ltr', $table_draft = false)
	{
		if (!$a) {
			if ($blockdir == 'rtl') {
				$a = 'R';
			} else {
				$a = 'L';
			}
		}
		$this->flowingBlockAttr['width'] = ($w * Mpdf::SCALE);
		// line height in user units
		$this->flowingBlockAttr['is_table'] = $is_table;
		$this->flowingBlockAttr['table_draft'] = $table_draft;
		$this->flowingBlockAttr['height'] = $h;
		$this->flowingBlockAttr['lineCount'] = 0;
		$this->flowingBlockAttr['align'] = $a;
		$this->flowingBlockAttr['font'] = [];
		$this->flowingBlockAttr['content'] = [];
		$this->flowingBlockAttr['contentB'] = [];
		$this->flowingBlockAttr['contentWidth'] = 0;
		$this->flowingBlockAttr['blockstate'] = $blockstate;

		$this->flowingBlockAttr['newblock'] = $newblock;
		$this->flowingBlockAttr['valign'] = 'M';
		$this->flowingBlockAttr['blockdir'] = $blockdir;
		$this->flowingBlockAttr['cOTLdata'] = []; // mPDF 5.7.1
		$this->flowingBlockAttr['lastBidiText'] = ''; // mPDF 5.7.1
		if (!empty($this->otl)) {
			$this->otl->lastBidiStrongType = '';
		} // *OTL*
	}

	function finishFlowingBlock($endofblock = false, $next = '')
	{
		$currentx = $this->x;
		// prints out the last chunk
		$is_table = $this->flowingBlockAttr['is_table'];
		$table_draft = $this->flowingBlockAttr['table_draft'];
		$maxWidth = & $this->flowingBlockAttr['width'];
		$stackHeight = & $this->flowingBlockAttr['height'];
		$align = & $this->flowingBlockAttr['align'];
		$content = & $this->flowingBlockAttr['content'];
		$contentB = & $this->flowingBlockAttr['contentB'];
		$font = & $this->flowingBlockAttr['font'];
		$contentWidth = & $this->flowingBlockAttr['contentWidth'];
		$lineCount = & $this->flowingBlockAttr['lineCount'];
		$valign = & $this->flowingBlockAttr['valign'];
		$blockstate = $this->flowingBlockAttr['blockstate'];

		$cOTLdata = & $this->flowingBlockAttr['cOTLdata']; // mPDF 5.7.1
		$newblock = $this->flowingBlockAttr['newblock'];
		$blockdir = $this->flowingBlockAttr['blockdir'];

		// *********** BLOCK BACKGROUND COLOR *****************//
		if ($this->blk[$this->blklvl]['bgcolor'] && !$is_table) {
			$fill = 0;
		} else {
			$this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings));
			$fill = 0;
		}

		$hanger = '';
		// Always right trim!
		// Right trim last content and adjust width if needed to justify (later)
		if (isset($content[count($content) - 1]) && preg_match('/[ ]+$/', $content[count($content) - 1], $m)) {
			$strip = strlen($m[0]);
			$content[count($content) - 1] = substr($content[count($content) - 1], 0, (strlen($content[count($content) - 1]) - $strip));
			/* -- OTL -- */
			if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
				$this->otl->trimOTLdata($cOTLdata[count($cOTLdata) - 1], false, true);
			}
			/* -- END OTL -- */
		}

		// the amount of space taken up so far in user units
		$usedWidth = 0;

		// COLS
		$oldcolumn = $this->CurrCol;

		if ($this->ColActive && !$is_table) {
			$this->breakpoints[$this->CurrCol][] = $this->y;
		} // *COLUMNS*
		// Print out each chunk

		/* -- TABLES -- */
		if ($is_table) {
			$ipaddingL = 0;
			$ipaddingR = 0;
			$paddingL = 0;
			$paddingR = 0;
		} else {
			/* -- END TABLES -- */
			$ipaddingL = $this->blk[$this->blklvl]['padding_left'];
			$ipaddingR = $this->blk[$this->blklvl]['padding_right'];
			$paddingL = ($ipaddingL * Mpdf::SCALE);
			$paddingR = ($ipaddingR * Mpdf::SCALE);
			$this->cMarginL = $this->blk[$this->blklvl]['border_left']['w'];
			$this->cMarginR = $this->blk[$this->blklvl]['border_right']['w'];

			// Added mPDF 3.0 Float DIV
			$fpaddingR = 0;
			$fpaddingL = 0;
			/* -- CSS-FLOAT -- */
			if (count($this->floatDivs)) {
				list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
				if ($r_exists) {
					$fpaddingR = $r_width;
				}
				if ($l_exists) {
					$fpaddingL = $l_width;
				}
			}
			/* -- END CSS-FLOAT -- */

			$usey = $this->y + 0.002;
			if (($newblock) && ($blockstate == 1 || $blockstate == 3) && ($lineCount == 0)) {
				$usey += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
			}
			/* -- CSS-IMAGE-FLOAT -- */
			// If float exists at this level
			if (isset($this->floatmargins['R']) && $usey <= $this->floatmargins['R']['y1'] && $usey >= $this->floatmargins['R']['y0'] && !$this->floatmargins['R']['skipline']) {
				$fpaddingR += $this->floatmargins['R']['w'];
			}
			if (isset($this->floatmargins['L']) && $usey <= $this->floatmargins['L']['y1'] && $usey >= $this->floatmargins['L']['y0'] && !$this->floatmargins['L']['skipline']) {
				$fpaddingL += $this->floatmargins['L']['w'];
			}
			/* -- END CSS-IMAGE-FLOAT -- */
		} // *TABLES*


		$lineBox = [];

		$this->_setInlineBlockHeights($lineBox, $stackHeight, $content, $font, $is_table);

		if ($is_table && count($content) == 0) {
			$stackHeight = 0;
		}

		if ($table_draft) {
			$this->y += $stackHeight;
			$this->objectbuffer = [];
			return 0;
		}

		// While we're at it, check if contains cursive text
		// Change NBSP to SPACE.
		// Re-calculate contentWidth
		$contentWidth = 0;

		foreach ($content as $k => $chunk) {
			$this->restoreFont($font[$k], false);
			if (!isset($this->objectbuffer[$k]) || (isset($this->objectbuffer[$k]) && !$this->objectbuffer[$k])) {
				// Soft Hyphens chr(173)
				if (!$this->usingCoreFont) {
					/* -- OTL -- */
					// mPDF 5.7.1
					if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
						$this->otl->removeChar($chunk, $cOTLdata[$k], "\xc2\xad");
						$this->otl->replaceSpace($chunk, $cOTLdata[$k]);
						$content[$k] = $chunk;
					} /* -- END OTL -- */ else {  // *OTL*
						$content[$k] = $chunk = str_replace("\xc2\xad", '', $chunk);
						$content[$k] = $chunk = str_replace(chr(194) . chr(160), chr(32), $chunk);
					} // *OTL*
				} elseif ($this->FontFamily != 'csymbol' && $this->FontFamily != 'czapfdingbats') {
					$content[$k] = $chunk = str_replace(chr(173), '', $chunk);
					$content[$k] = $chunk = str_replace(chr(160), chr(32), $chunk);
				}
				$contentWidth += $this->GetStringWidth($chunk, true, (isset($cOTLdata[$k]) ? $cOTLdata[$k] : false), $this->textvar) * Mpdf::SCALE;
			} elseif (isset($this->objectbuffer[$k]) && $this->objectbuffer[$k]) {
				// LIST MARKERS	// mPDF 6  Lists
				if ($this->objectbuffer[$k]['type'] == 'image' && isset($this->objectbuffer[$k]['listmarker']) && $this->objectbuffer[$k]['listmarker'] && $this->objectbuffer[$k]['listmarkerposition'] == 'outside') {
					// do nothing
				} else {
					$contentWidth += $this->objectbuffer[$k]['OUTER-WIDTH'] * Mpdf::SCALE;
				}
			}
		}

		if (isset($font[count($font) - 1])) {
			$lastfontreqstyle = (isset($font[count($font) - 1]['ReqFontStyle']) ? $font[count($font) - 1]['ReqFontStyle'] : '');
			$lastfontstyle = (isset($font[count($font) - 1]['style']) ? $font[count($font) - 1]['style'] : '');
		} else {
			$lastfontreqstyle = null;
			$lastfontstyle = null;
		}
		if ($blockdir == 'ltr' && strpos($lastfontreqstyle, "I") !== false && strpos($lastfontstyle, "I") === false) { // Artificial italic
			$lastitalic = $this->FontSize * 0.15 * Mpdf::SCALE;
		} else {
			$lastitalic = 0;
		}

		// Get PAGEBREAK TO TEST for height including the bottom border/padding
		$check_h = max($this->divheight, $stackHeight);

		// This fixes a proven bug...
		if ($endofblock && $newblock && $blockstate == 0 && !$content) {
			$check_h = 0;
		}
		// but ? needs to fix potentially more widespread...
		// if (!$content) {  $check_h = 0; }

		if ($this->blklvl > 0 && !$is_table) {
			if ($endofblock && $blockstate > 1) {
				if ($this->blk[$this->blklvl]['page_break_after_avoid']) {
					$check_h += $stackHeight;
				}
				$check_h += ($this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w']);
			}
			if (($newblock && ($blockstate == 1 || $blockstate == 3) && $lineCount == 0) || ($endofblock && $blockstate == 3 && $lineCount == 0)) {
				$check_h += ($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['border_top']['w']);
			}
		}

		// Force PAGE break if column height cannot take check-height
		if ($this->ColActive && $check_h > ($this->PageBreakTrigger - $this->y0)) {
			$this->SetCol($this->NbCol - 1);
		}

		// Avoid just border/background-color moved on to next page
		if ($endofblock && $blockstate > 1 && !$content) {
			$buff = $this->margBuffer;
		} else {
			$buff = 0;
		}


		// PAGEBREAK
		if (!$is_table && ($this->y + $check_h) > ($this->PageBreakTrigger + $buff) and ! $this->InFooter and $this->AcceptPageBreak()) {
			$bak_x = $this->x; // Current X position
			// WORD SPACING
			$ws = $this->ws; // Word Spacing
			$charspacing = $this->charspacing; // Character Spacing
			$this->ResetSpacing();

			$this->AddPage($this->CurOrientation);

			$this->x = $bak_x;
			// Added to correct for OddEven Margins
			$currentx += $this->MarginCorrection;
			$this->x += $this->MarginCorrection;

			// WORD SPACING
			$this->SetSpacing($charspacing, $ws);
		}


		/* -- COLUMNS -- */
		// COLS
		// COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			$currentx += $this->ChangeColumn * ($this->ColWidth + $this->ColGap);
			$this->x += $this->ChangeColumn * ($this->ColWidth + $this->ColGap);
			$oldcolumn = $this->CurrCol;
		}


		if ($this->ColActive && !$is_table) {
			$this->breakpoints[$this->CurrCol][] = $this->y;
		}
		/* -- END COLUMNS -- */

		// TOP MARGIN
		if ($newblock && ($blockstate == 1 || $blockstate == 3) && ($this->blk[$this->blklvl]['margin_top']) && $lineCount == 0 && !$is_table) {
			$this->DivLn($this->blk[$this->blklvl]['margin_top'], $this->blklvl - 1, true, $this->blk[$this->blklvl]['margin_collapse']);
			if ($this->ColActive) {
				$this->breakpoints[$this->CurrCol][] = $this->y;
			} // *COLUMNS*
		}

		if ($newblock && ($blockstate == 1 || $blockstate == 3) && $lineCount == 0 && !$is_table) {
			$this->blk[$this->blklvl]['y0'] = $this->y;
			$this->blk[$this->blklvl]['startpage'] = $this->page;
			if ($this->blk[$this->blklvl]['float']) {
				$this->blk[$this->blklvl]['float_start_y'] = $this->y;
			}
			if ($this->ColActive) {
				$this->breakpoints[$this->CurrCol][] = $this->y;
			} // *COLUMNS*
		}

		// Paragraph INDENT
		$WidthCorrection = 0;
		if (($newblock) && ($blockstate == 1 || $blockstate == 3) && isset($this->blk[$this->blklvl]['text_indent']) && ($lineCount == 0) && (!$is_table) && ($align != 'C')) {
			$ti = $this->sizeConverter->convert($this->blk[$this->blklvl]['text_indent'], $this->blk[$this->blklvl]['inner_width'], $this->blk[$this->blklvl]['InlineProperties']['size'], false);  // mPDF 5.7.4
			$WidthCorrection = ($ti * Mpdf::SCALE);
		}


		// PADDING and BORDER spacing/fill
		if (($newblock) && ($blockstate == 1 || $blockstate == 3) && (($this->blk[$this->blklvl]['padding_top']) || ($this->blk[$this->blklvl]['border_top'])) && ($lineCount == 0) && (!$is_table)) {
			// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
			$this->DivLn($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'], -3, true, false, 1);
			if ($this->ColActive) {
				$this->breakpoints[$this->CurrCol][] = $this->y;
			} // *COLUMNS*
			$this->x = $currentx;
		}


		// Added mPDF 3.0 Float DIV
		$fpaddingR = 0;
		$fpaddingL = 0;
		/* -- CSS-FLOAT -- */
		if (count($this->floatDivs)) {
			list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
			if ($r_exists) {
				$fpaddingR = $r_width;
			}
			if ($l_exists) {
				$fpaddingL = $l_width;
			}
		}
		/* -- END CSS-FLOAT -- */

		$usey = $this->y + 0.002;
		if (($newblock) && ($blockstate == 1 || $blockstate == 3) && ($lineCount == 0)) {
			$usey += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
		}
		/* -- CSS-IMAGE-FLOAT -- */
		// If float exists at this level
		if (isset($this->floatmargins['R']) && $usey <= $this->floatmargins['R']['y1'] && $usey >= $this->floatmargins['R']['y0'] && !$this->floatmargins['R']['skipline']) {
			$fpaddingR += $this->floatmargins['R']['w'];
		}
		if (isset($this->floatmargins['L']) && $usey <= $this->floatmargins['L']['y1'] && $usey >= $this->floatmargins['L']['y0'] && !$this->floatmargins['L']['skipline']) {
			$fpaddingL += $this->floatmargins['L']['w'];
		}
		/* -- END CSS-IMAGE-FLOAT -- */


		if ($content) {
			// In FinishFlowing Block no lines are justified as it is always last line
			// but if CJKorphan has allowed content width to go over max width, use J charspacing to compress line
			// JUSTIFICATION J - NOT!
			$nb_carac = 0;
			$nb_spaces = 0;
			$jcharspacing = 0;
			$jkashida = 0;
			$jws = 0;
			$inclCursive = false;
			$dottab = false;
			foreach ($content as $k => $chunk) {
				if (!isset($this->objectbuffer[$k]) || (isset($this->objectbuffer[$k]) && !$this->objectbuffer[$k])) {
					$nb_carac += mb_strlen($chunk, $this->mb_enc);
					$nb_spaces += mb_substr_count($chunk, ' ', $this->mb_enc);
					// mPDF 6
					// Use GPOS OTL
					$this->restoreFont($font[$k], false);
					if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
						if (isset($cOTLdata[$k]['group']) && $cOTLdata[$k]['group']) {
							$nb_marks = substr_count($cOTLdata[$k]['group'], 'M');
							$nb_carac -= $nb_marks;
						}
						if (preg_match("/([" . $this->pregCURSchars . "])/u", $chunk)) {
							$inclCursive = true;
						}
					}
				} else {
					$nb_carac ++;  // mPDF 6 allow spacing for inline object
					if ($this->objectbuffer[$k]['type'] == 'dottab') {
						$dottab = $this->objectbuffer[$k]['outdent'];
					}
				}
			}

			// DIRECTIONALITY RTL
			$chunkorder = range(0, count($content) - 1); // mPDF 6
			/* -- OTL -- */
			// mPDF 6
			if ($blockdir == 'rtl' || $this->biDirectional) {
				$this->otl->bidiReorder($chunkorder, $content, $cOTLdata, $blockdir);
				// From this point on, $content and $cOTLdata may contain more elements (and re-ordered) compared to
				// $this->objectbuffer and $font ($chunkorder contains the mapping)
			}
			/* -- END OTL -- */

			// Remove any XAdvance from OTL data at end of line
			// And correct for XPlacement on last character
			// BIDI is applied
			foreach ($chunkorder as $aord => $k) {
				if (count($cOTLdata)) {
					$this->restoreFont($font[$k], false);
					// ...FinishFlowingBlock...
					if ($aord == count($chunkorder) - 1 && isset($cOTLdata[$aord]['group'])) { // Last chunk on line
						$nGPOS = strlen($cOTLdata[$aord]['group']) - 1; // Last character
						if (isset($cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL']) || isset($cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceR'])) {
							if (isset($cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL'])) {
								$w = $cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL'] * 1000 / $this->CurrentFont['unitsPerEm'];
							} else {
								$w = $cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceR'] * 1000 / $this->CurrentFont['unitsPerEm'];
							}
							$w *= ($this->FontSize / 1000);
							$contentWidth -= $w * Mpdf::SCALE;
							$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL'] = 0;
							$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceR'] = 0;
						}

						// If last character has an XPlacement set, adjust width calculation, and add to XAdvance to account for it
						if (isset($cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XPlacement'])) {
							$w = -$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XPlacement'] * 1000 / $this->CurrentFont['unitsPerEm'];
							$w *= ($this->FontSize / 1000);
							$contentWidth -= $w * Mpdf::SCALE;
							$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL'] = $cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XPlacement'];
							$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceR'] = $cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XPlacement'];
						}
					}
				}
			}

			// if it's justified, we need to find the char/word spacing (or if orphans have allowed length of line to go over the maxwidth)
			// If "orphans" in fact is just a final space - ignore this
			$lastchar = mb_substr($content[(count($chunkorder) - 1)], mb_strlen($content[(count($chunkorder) - 1)], $this->mb_enc) - 1, 1, $this->mb_enc);
			if (preg_match("/[" . $this->CJKoverflow . "]/u", $lastchar)) {
				$CJKoverflow = true;
			} else {
				$CJKoverflow = false;
			}
			if ((((($contentWidth + $lastitalic) > $maxWidth) && ($content[(count($chunkorder) - 1)] != ' ') ) ||
				(!$endofblock && $align == 'J' && ($next == 'image' || $next == 'select' || $next == 'input' || $next == 'textarea' || ($next == 'br' && $this->justifyB4br)))) && !($CJKoverflow && $this->allowCJKoverflow)) {
				// WORD SPACING
				list($jcharspacing, $jws, $jkashida) = $this->GetJspacing($nb_carac, $nb_spaces, ($maxWidth - $lastitalic - $contentWidth - $WidthCorrection - (($this->cMarginL + $this->cMarginR) * Mpdf::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * Mpdf::SCALE) )), $inclCursive, $cOTLdata);
			} /* -- CJK-FONTS -- */ elseif ($this->checkCJK && $align == 'J' && $CJKoverflow && $this->allowCJKoverflow && $this->CJKforceend) {
				// force-end overhang
				$hanger = mb_substr($content[(count($chunkorder) - 1)], mb_strlen($content[(count($chunkorder) - 1)], $this->mb_enc) - 1, 1, $this->mb_enc);
				if (preg_match("/[" . $this->CJKoverflow . "]/u", $hanger)) {
					$content[(count($chunkorder) - 1)] = mb_substr($content[(count($chunkorder) - 1)], 0, mb_strlen($content[(count($chunkorder) - 1)], $this->mb_enc) - 1, $this->mb_enc);
					$this->restoreFont($font[$chunkorder[count($chunkorder) - 1]], false);
					$contentWidth -= $this->GetStringWidth($hanger) * Mpdf::SCALE;
					$nb_carac -= 1;
					list($jcharspacing, $jws, $jkashida) = $this->GetJspacing($nb_carac, $nb_spaces, ($maxWidth - $lastitalic - $contentWidth - $WidthCorrection - (($this->cMarginL + $this->cMarginR) * Mpdf::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * Mpdf::SCALE) )), $inclCursive, $cOTLdata);
				}
			} /* -- END CJK-FONTS -- */

			// Check if will fit at word/char spacing of previous line - if so continue it
			// but only allow a maximum of $this->jSmaxWordLast and $this->jSmaxCharLast
			elseif ($contentWidth < ($maxWidth - $lastitalic - $WidthCorrection - (($this->cMarginL + $this->cMarginR) * Mpdf::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * Mpdf::SCALE))) && !$this->fixedlSpacing) {
				if ($this->ws > $this->jSmaxWordLast) {
					$jws = $this->jSmaxWordLast;
				}
				if ($this->charspacing > $this->jSmaxCharLast) {
					$jcharspacing = $this->jSmaxCharLast;
				}
				$check = $maxWidth - $lastitalic - $WidthCorrection - $contentWidth - (($this->cMarginL + $this->cMarginR) * Mpdf::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * Mpdf::SCALE) ) - ( $jcharspacing * $nb_carac) - ( $jws * $nb_spaces);
				if ($check <= 0) {
					$jcharspacing = 0;
					$jws = 0;
				}
			}

			$empty = $maxWidth - $lastitalic - $WidthCorrection - $contentWidth - (($this->cMarginL + $this->cMarginR) * Mpdf::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * Mpdf::SCALE) );


			$empty -= ($jcharspacing * ($nb_carac - 1)); // mPDF 6 nb_carac MINUS 1
			$empty -= ($jws * $nb_spaces);
			$empty -= ($jkashida);

			$empty /= Mpdf::SCALE;

			if (!$is_table) {
				$this->maxPosR = max($this->maxPosR, ($this->w - $this->rMargin - $this->blk[$this->blklvl]['outer_right_margin'] - $empty));
				$this->maxPosL = min($this->maxPosL, ($this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'] + $empty));
			}

			$arraysize = count($chunkorder);

			$margins = ($this->cMarginL + $this->cMarginR) + ($ipaddingL + $ipaddingR + $fpaddingR + $fpaddingR );

			if (!$is_table) {
				$this->DivLn($stackHeight, $this->blklvl, false);
			} // false -> don't advance y

			$this->x = $currentx + $this->cMarginL + $ipaddingL + $fpaddingL;
			if ($dottab !== false && $blockdir == 'rtl') {
				$this->x -= $dottab;
			} elseif ($align == 'R') {
				$this->x += $empty;
			} elseif ($align == 'J' && $blockdir == 'rtl') {
				$this->x += $empty;
			} elseif ($align == 'C') {
				$this->x += ($empty / 2);
			}

			// Paragraph INDENT
			$WidthCorrection = 0;
			if (($newblock) && ($blockstate == 1 || $blockstate == 3) && isset($this->blk[$this->blklvl]['text_indent']) && ($lineCount == 0) && (!$is_table) && ($align != 'C')) {
				$ti = $this->sizeConverter->convert($this->blk[$this->blklvl]['text_indent'], $this->blk[$this->blklvl]['inner_width'], $this->blk[$this->blklvl]['InlineProperties']['size'], false);  // mPDF 5.7.4
				if ($blockdir != 'rtl') {
					$this->x += $ti;
				} // mPDF 6
			}

			foreach ($chunkorder as $aord => $k) { // mPDF 5.7
				$chunk = $content[$aord];
				if (isset($this->objectbuffer[$k]) && $this->objectbuffer[$k]) {
					$xadj = $this->x - $this->objectbuffer[$k]['OUTER-X'];
					$this->objectbuffer[$k]['OUTER-X'] += $xadj;
					$this->objectbuffer[$k]['BORDER-X'] += $xadj;
					$this->objectbuffer[$k]['INNER-X'] += $xadj;

					if ($this->objectbuffer[$k]['type'] == 'listmarker') {
						$this->objectbuffer[$k]['lineBox'] = $lineBox[-1]; // Block element details for glyph-origin
					}
					$yadj = $this->y - $this->objectbuffer[$k]['OUTER-Y'];
					if ($this->objectbuffer[$k]['type'] == 'dottab') { // mPDF 6 DOTTAB
						$this->objectbuffer[$k]['lineBox'] = $lineBox[$k]; // element details for glyph-origin
					}
					if ($this->objectbuffer[$k]['type'] != 'dottab') { // mPDF 6 DOTTAB
						$yadj += $lineBox[$k]['top'];
					}
					$this->objectbuffer[$k]['OUTER-Y'] += $yadj;
					$this->objectbuffer[$k]['BORDER-Y'] += $yadj;
					$this->objectbuffer[$k]['INNER-Y'] += $yadj;
				}

				$this->restoreFont($font[$k]);  // mPDF 5.7

				if ($is_table && substr($align, 0, 1) == 'D' && $aord == 0) {
					$dp = $this->decimal_align[substr($align, 0, 2)];
					$s = preg_split('/' . preg_quote($dp, '/') . '/', $content[0], 2);  // ? needs to be /u if not core
					$s0 = $this->GetStringWidth($s[0], false);
					$this->x += ($this->decimal_offset - $s0);
				}

				$this->SetSpacing(($this->fixedlSpacing * Mpdf::SCALE) + $jcharspacing, ($this->fixedlSpacing + $this->minwSpacing) * Mpdf::SCALE + $jws);
				$this->fixedlSpacing = false;
				$this->minwSpacing = 0;

				$save_vis = $this->visibility;
				if (isset($this->textparam['visibility']) && $this->textparam['visibility'] && $this->textparam['visibility'] != $this->visibility) {
					$this->SetVisibility($this->textparam['visibility']);
				}

				// *********** SPAN BACKGROUND COLOR ***************** //
				if (isset($this->spanbgcolor) && $this->spanbgcolor) {
					$cor = $this->spanbgcolorarray;
					$this->SetFColor($cor);
					$save_fill = $fill;
					$spanfill = 1;
					$fill = 1;
				}
				if (!empty($this->spanborddet)) {
					if (strpos($contentB[$k], 'L') !== false && isset($this->spanborddet['L'])) {
						$this->x += $this->spanborddet['L']['w'];
					}
					if (strpos($contentB[$k], 'L') === false) {
						$this->spanborddet['L']['s'] = $this->spanborddet['L']['w'] = 0;
					}
					if (strpos($contentB[$k], 'R') === false) {
						$this->spanborddet['R']['s'] = $this->spanborddet['R']['w'] = 0;
					}
				}
				// WORD SPACING
				// mPDF 5.7.1
				$stringWidth = $this->GetStringWidth($chunk, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar);
				$nch = mb_strlen($chunk, $this->mb_enc);
				// Use GPOS OTL
				if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
					if (isset($cOTLdata[$aord]['group']) && $cOTLdata[$aord]['group']) {
						$nch -= substr_count($cOTLdata[$aord]['group'], 'M');
					}
				}
				$stringWidth += ( $this->charspacing * $nch / Mpdf::SCALE );

				$stringWidth += ( $this->ws * mb_substr_count($chunk, ' ', $this->mb_enc) / Mpdf::SCALE );

				if (isset($this->objectbuffer[$k])) {
					if ($this->objectbuffer[$k]['type'] == 'dottab') {
						$this->objectbuffer[$k]['OUTER-WIDTH'] +=$empty;
						$this->objectbuffer[$k]['OUTER-WIDTH'] +=$this->objectbuffer[$k]['outdent'];
					}
					// LIST MARKERS	// mPDF 6  Lists
					if ($this->objectbuffer[$k]['type'] == 'image' && isset($this->objectbuffer[$k]['listmarker']) && $this->objectbuffer[$k]['listmarker'] && $this->objectbuffer[$k]['listmarkerposition'] == 'outside') {
						// do nothing
					} else {
						$stringWidth = $this->objectbuffer[$k]['OUTER-WIDTH'];
					}
				}

				if ($stringWidth == 0) {
					$stringWidth = 0.000001;
				}
				if ($aord == $arraysize - 1) { // mPDF 5.7
					// mPDF 5.7.1
					if ($this->checkCJK && $CJKoverflow && $align == 'J' && $this->allowCJKoverflow && $hanger && $this->CJKforceend) {
						// force-end overhang
						$this->Cell($stringWidth, $stackHeight, $chunk, '', 0, '', $fill, $this->HREF, $currentx, 0, 0, 'M', $fill, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar, (isset($lineBox[$k]) ? $lineBox[$k] : false));  // mPDF 5.7.1
						$this->Cell($this->GetStringWidth($hanger), $stackHeight, $hanger, '', 1, '', $fill, $this->HREF, $currentx, 0, 0, 'M', $fill, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar, (isset($lineBox[$k]) ? $lineBox[$k] : false)); // mPDF 5.7.1
					} else {
						$this->Cell($stringWidth, $stackHeight, $chunk, '', 1, '', $fill, $this->HREF, $currentx, 0, 0, 'M', $fill, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar, (isset($lineBox[$k]) ? $lineBox[$k] : false)); // mPDF 5.7.1
					}
				} else {
					$this->Cell($stringWidth, $stackHeight, $chunk, '', 0, '', $fill, $this->HREF, 0, 0, 0, 'M', $fill, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar, (isset($lineBox[$k]) ? $lineBox[$k] : false)); // first or middle part	// mPDF 5.7.1
				}


				if (!empty($this->spanborddet)) {
					if (strpos($contentB[$k], 'R') !== false && $aord != $arraysize - 1) {
						$this->x += $this->spanborddet['R']['w'];
					}
				}
				// *********** SPAN BACKGROUND COLOR OFF - RESET BLOCK BGCOLOR ***************** //
				if (isset($spanfill) && $spanfill) {
					$fill = $save_fill;
					$spanfill = 0;
					if ($fill) {
						$this->SetFColor($bcor);
					}
				}
				if (isset($this->textparam['visibility']) && $this->textparam['visibility'] && $this->visibility != $save_vis) {
					$this->SetVisibility($save_vis);
				}
			}

			$this->printobjectbuffer($is_table, $blockdir);
			$this->objectbuffer = [];
			$this->ResetSpacing();
		} // END IF CONTENT

		/* -- CSS-IMAGE-FLOAT -- */
		// Update values if set to skipline
		if ($this->floatmargins) {
			$this->_advanceFloatMargins();
		}


		if ($endofblock && $blockstate > 1) {
			// If float exists at this level
			if (isset($this->floatmargins['R']['y1'])) {
				$fry1 = $this->floatmargins['R']['y1'];
			} else {
				$fry1 = 0;
			}
			if (isset($this->floatmargins['L']['y1'])) {
				$fly1 = $this->floatmargins['L']['y1'];
			} else {
				$fly1 = 0;
			}
			if ($this->y < $fry1 || $this->y < $fly1) {
				$drop = max($fry1, $fly1) - $this->y;
				$this->DivLn($drop);
				$this->x = $currentx;
			}
		}
		/* -- END CSS-IMAGE-FLOAT -- */


		// PADDING and BORDER spacing/fill
		if ($endofblock && ($blockstate > 1) && ($this->blk[$this->blklvl]['padding_bottom'] || $this->blk[$this->blklvl]['border_bottom'] || $this->blk[$this->blklvl]['css_set_height']) && (!$is_table)) {
			// If CSS height set, extend bottom - if on same page as block started, and CSS HEIGHT > actual height,
			// and does not force pagebreak
			$extra = 0;
			if (isset($this->blk[$this->blklvl]['css_set_height']) && $this->blk[$this->blklvl]['css_set_height'] && $this->blk[$this->blklvl]['startpage'] == $this->page) {
				// predicted height
				$h1 = ($this->y - $this->blk[$this->blklvl]['y0']) + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w'];
				if ($h1 < ($this->blk[$this->blklvl]['css_set_height'] + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['padding_top'])) {
					$extra = ($this->blk[$this->blklvl]['css_set_height'] + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['padding_top']) - $h1;
				}
				if ($this->y + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w'] + $extra > $this->PageBreakTrigger) {
					$extra = $this->PageBreakTrigger - ($this->y + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w']);
				}
			}

			// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
			$this->DivLn($this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w'] + $extra, -3, true, false, 2);
			$this->x = $currentx;

			if ($this->ColActive) {
				$this->breakpoints[$this->CurrCol][] = $this->y;
			} // *COLUMNS*
		}

		// SET Bottom y1 of block (used for painting borders)
		if (($endofblock) && ($blockstate > 1) && (!$is_table)) {
			$this->blk[$this->blklvl]['y1'] = $this->y;
		}

		// BOTTOM MARGIN
		if (($endofblock) && ($blockstate > 1) && ($this->blk[$this->blklvl]['margin_bottom']) && (!$is_table)) {
			if ($this->y + $this->blk[$this->blklvl]['margin_bottom'] < $this->PageBreakTrigger and ! $this->InFooter) {
				$this->DivLn($this->blk[$this->blklvl]['margin_bottom'], $this->blklvl - 1, true, $this->blk[$this->blklvl]['margin_collapse']);
				if ($this->ColActive) {
					$this->breakpoints[$this->CurrCol][] = $this->y;
				} // *COLUMNS*
			}
		}

		// Reset lineheight
		$stackHeight = $this->divheight;
	}

	function printobjectbuffer($is_table = false, $blockdir = false)
	{
		if (!$blockdir) {
			$blockdir = $this->directionality;
		}

		if ($is_table && $this->shrin_k > 1) {
			$k = $this->shrin_k;
		} else {
			$k = 1;
		}

		$save_y = $this->y;
		$save_x = $this->x;

		$save_currentfontfamily = $this->FontFamily;
		$save_currentfontsize = $this->FontSizePt;
		$save_currentfontstyle = $this->FontStyle;

		if ($blockdir == 'rtl') {
			$rtlalign = 'R';
		} else {
			$rtlalign = 'L';
		}

		foreach ($this->objectbuffer as $ib => $objattr) {

			if ($objattr['type'] == 'bookmark' || $objattr['type'] == 'indexentry' || $objattr['type'] == 'toc') {
				$x = $objattr['OUTER-X'];
				$y = $objattr['OUTER-Y'];
				$this->y = $y - $this->FontSize / 2;
				$this->x = $x;
				if ($objattr['type'] == 'bookmark') {
					$this->Bookmark($objattr['CONTENT'], $objattr['bklevel'], $y - $this->FontSize);
				} // *BOOKMARKS*
				if ($objattr['type'] == 'indexentry') {
					$this->IndexEntry($objattr['CONTENT']);
				} // *INDEX*
				if ($objattr['type'] == 'toc') {
					$this->TOC_Entry($objattr['CONTENT'], $objattr['toclevel'], (isset($objattr['toc_id']) ? $objattr['toc_id'] : ''));
				} // *TOC*
			} /* -- ANNOTATIONS -- */ elseif ($objattr['type'] == 'annot') {
				if ($objattr['POS-X']) {
					$x = $objattr['POS-X'];
				} elseif ($this->annotMargin <> 0) {
					$x = -$objattr['OUTER-X'];
				} else {
					$x = $objattr['OUTER-X'];
				}
				if ($objattr['POS-Y']) {
					$y = $objattr['POS-Y'];
				} else {
					$y = $objattr['OUTER-Y'] - $this->FontSize / 2;
				}
				// Create a dummy entry in the _out/columnBuffer with position sensitive data,
				// linking $y-1 in the Columnbuffer with entry in $this->columnAnnots
				// and when columns are split in length will not break annotation from current line
				$this->y = $y - 1;
				$this->x = $x - 1;
				$this->Line($x - 1, $y - 1, $x - 1, $y - 1);
				$this->Annotation($objattr['CONTENT'], $x, $y, $objattr['ICON'], $objattr['AUTHOR'], $objattr['SUBJECT'], $objattr['OPACITY'], $objattr['COLOR'], (isset($objattr['POPUP']) ? $objattr['POPUP'] : ''), (isset($objattr['FILE']) ? $objattr['FILE'] : ''));
			} /* -- END ANNOTATIONS -- */ else {
				$y = $objattr['OUTER-Y'];
				$x = $objattr['OUTER-X'];
				$w = $objattr['OUTER-WIDTH'];
				$h = $objattr['OUTER-HEIGHT'];
				if (isset($objattr['text'])) {
					$texto = $objattr['text'];
				}
				$this->y = $y;
				$this->x = $x;
				if (isset($objattr['fontfamily'])) {
					$this->SetFont($objattr['fontfamily'], '', $objattr['fontsize']);
				}
			}

			// HR
			if ($objattr['type'] == 'hr') {
				$this->SetDColor($objattr['color']);
				switch ($objattr['align']) {
					case 'C':
						$empty = $objattr['OUTER-WIDTH'] - $objattr['INNER-WIDTH'];
						$empty /= 2;
						$x += $empty;
						break;
					case 'R':
						$empty = $objattr['OUTER-WIDTH'] - $objattr['INNER-WIDTH'];
						$x += $empty;
						break;
				}
				$oldlinewidth = $this->LineWidth;
				$this->SetLineWidth($objattr['linewidth'] / $k);
				$this->y += ($objattr['linewidth'] / 2) + $objattr['margin_top'] / $k;
				$this->Line($x, $this->y, $x + $objattr['INNER-WIDTH'], $this->y);
				$this->SetLineWidth($oldlinewidth);
				$this->SetDColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
			}
			// IMAGE
			if ($objattr['type'] == 'image') {
				// mPDF 5.7.3 TRANSFORMS
				if (isset($objattr['transform'])) {
					$this->writer->write("\n" . '% BTR'); // Begin Transform
				}
				if (isset($objattr['z-index']) && $objattr['z-index'] > 0 && $this->current_layer == 0) {
					$this->BeginLayer($objattr['z-index']);
				}
				if (isset($objattr['visibility']) && $objattr['visibility'] != 'visible' && $objattr['visibility']) {
					$this->SetVisibility($objattr['visibility']);
				}
				if (isset($objattr['opacity'])) {
					$this->SetAlpha($objattr['opacity']);
				}

				$obiw = $objattr['INNER-WIDTH'];
				$obih = $objattr['INNER-HEIGHT'];

				$sx = $objattr['orig_w'] ? ($objattr['INNER-WIDTH'] * Mpdf::SCALE / $objattr['orig_w']) : INF;
				$sy = $objattr['orig_h'] ? ($objattr['INNER-HEIGHT'] * Mpdf::SCALE / $objattr['orig_h']) : INF;

				$rotate = 0;
				if (isset($objattr['ROTATE'])) {
					$rotate = $objattr['ROTATE'];
				}

				if ($rotate == 90) {
					// Clockwise
					$obiw = $objattr['INNER-HEIGHT'];
					$obih = $objattr['INNER-WIDTH'];
					$tr = $this->transformTranslate(0, -$objattr['INNER-WIDTH'], true);
					$tr .= ' ' . $this->transformRotate(90, $objattr['INNER-X'], ($objattr['INNER-Y'] + $objattr['INNER-WIDTH']), true);
					$sx = $obiw * Mpdf::SCALE / $objattr['orig_h'];
					$sy = $obih * Mpdf::SCALE / $objattr['orig_w'];
				} elseif ($rotate == -90 || $rotate == 270) {
					// AntiClockwise
					$obiw = $objattr['INNER-HEIGHT'];
					$obih = $objattr['INNER-WIDTH'];
					$tr = $this->transformTranslate($objattr['INNER-WIDTH'], ($objattr['INNER-HEIGHT'] - $objattr['INNER-WIDTH']), true);
					$tr .= ' ' . $this->transformRotate(-90, $objattr['INNER-X'], ($objattr['INNER-Y'] + $objattr['INNER-WIDTH']), true);
					$sx = $obiw * Mpdf::SCALE / $objattr['orig_h'];
					$sy = $obih * Mpdf::SCALE / $objattr['orig_w'];
				} elseif ($rotate == 180) {
					// Mirror
					$tr = $this->transformTranslate($objattr['INNER-WIDTH'], -$objattr['INNER-HEIGHT'], true);
					$tr .= ' ' . $this->transformRotate(180, $objattr['INNER-X'], ($objattr['INNER-Y'] + $objattr['INNER-HEIGHT']), true);
				} else {
					$tr = '';
				}
				$tr = trim($tr);
				if ($tr) {
					$tr .= ' ';
				}
				$gradmask = '';

				// mPDF 5.7.3 TRANSFORMS
				$tr2 = '';
				if (isset($objattr['transform'])) {
					$maxsize_x = $w;
					$maxsize_y = $h;
					$cx = $x + $w / 2;
					$cy = $y + $h / 2;
					preg_match_all('/(translatex|translatey|translate|scalex|scaley|scale|rotate|skewX|skewY|skew)\((.*?)\)/is', $objattr['transform'], $m);
					if (count($m[0])) {
						for ($i = 0; $i < count($m[0]); $i++) {
							$c = strtolower($m[1][$i]);
							$v = trim($m[2][$i]);
							$vv = preg_split('/[ ,]+/', $v);
							if ($c == 'translate' && count($vv)) {
								$translate_x = $this->sizeConverter->convert($vv[0], $maxsize_x, false, false);
								if (count($vv) == 2) {
									$translate_y = $this->sizeConverter->convert($vv[1], $maxsize_y, false, false);
								} else {
									$translate_y = 0;
								}
								$tr2 .= $this->transformTranslate($translate_x, $translate_y, true) . ' ';
							} elseif ($c == 'translatex' && count($vv)) {
								$translate_x = $this->sizeConverter->convert($vv[0], $maxsize_x, false, false);
								$tr2 .= $this->transformTranslate($translate_x, 0, true) . ' ';
							} elseif ($c == 'translatey' && count($vv)) {
								$translate_y = $this->sizeConverter->convert($vv[1], $maxsize_y, false, false);
								$tr2 .= $this->transformTranslate(0, $translate_y, true) . ' ';
							} elseif ($c == 'scale' && count($vv)) {
								$scale_x = $vv[0] * 100;
								if (count($vv) == 2) {
									$scale_y = $vv[1] * 100;
								} else {
									$scale_y = $scale_x;
								}
								$tr2 .= $this->transformScale($scale_x, $scale_y, $cx, $cy, true) . ' ';
							} elseif ($c == 'scalex' && count($vv)) {
								$scale_x = $vv[0] * 100;
								$tr2 .= $this->transformScale($scale_x, 0, $cx, $cy, true) . ' ';
							} elseif ($c == 'scaley' && count($vv)) {
								$scale_y = $vv[1] * 100;
								$tr2 .= $this->transformScale(0, $scale_y, $cx, $cy, true) . ' ';
							} elseif ($c == 'skew' && count($vv)) {
								$angle_x = $this->ConvertAngle($vv[0], false);
								if (count($vv) == 2) {
									$angle_y = $this->ConvertAngle($vv[1], false);
								} else {
									$angle_y = 0;
								}
								$tr2 .= $this->transformSkew($angle_x, $angle_y, $cx, $cy, true) . ' ';
							} elseif ($c == 'skewx' && count($vv)) {
								$angle = $this->ConvertAngle($vv[0], false);
								$tr2 .= $this->transformSkew($angle, 0, $cx, $cy, true) . ' ';
							} elseif ($c == 'skewy' && count($vv)) {
								$angle = $this->ConvertAngle($vv[0], false);
								$tr2 .= $this->transformSkew(0, $angle, $cx, $cy, true) . ' ';
							} elseif ($c == 'rotate' && count($vv)) {
								$angle = $this->ConvertAngle($vv[0]);
								$tr2 .= $this->transformRotate($angle, $cx, $cy, true) . ' ';
							}
						}
					}
				}

				// LIST MARKERS (Images)	// mPDF 6  Lists
				if (isset($objattr['listmarker']) && $objattr['listmarker'] && $objattr['listmarkerposition'] == 'outside') {
					$mw = $objattr['OUTER-WIDTH'];
					// NB If change marker-offset, also need to alter in function _getListMarkerWidth
					$adjx = $this->sizeConverter->convert($this->list_marker_offset, $this->FontSize);
					if ($objattr['dir'] == 'rtl') {
						$objattr['INNER-X'] += $adjx;
					} else {
						$objattr['INNER-X'] -= $adjx;
						$objattr['INNER-X'] -= $mw;
					}
				}
				// mPDF 5.7.3 TRANSFORMS / BACKGROUND COLOR
				// Transform also affects image background
				if ($tr2) {
					$this->writer->write('q ' . $tr2 . ' ');
				}
				if (isset($objattr['bgcolor']) && $objattr['bgcolor']) {
					$bgcol = $objattr['bgcolor'];
					$this->SetFColor($bgcol);
					$this->Rect($x, $y, $w, $h, 'F');
					$this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings));
				}
				if ($tr2) {
					$this->writer->write('Q');
				}

				/* -- BACKGROUNDS -- */
				if (isset($objattr['GRADIENT-MASK'])) {
					$g = $this->gradient->parseMozGradient($objattr['GRADIENT-MASK']);
					if ($g) {
						$dummy = $this->gradient->Gradient($objattr['INNER-X'], $objattr['INNER-Y'], $obiw, $obih, $g['type'], $g['stops'], $g['colorspace'], $g['coords'], $g['extend'], true, true);
						$gradmask = '/TGS' . count($this->gradients) . ' gs ';
					}
				}
				/* -- END BACKGROUNDS -- */
				/* -- IMAGES-WMF -- */
				if (isset($objattr['itype']) && $objattr['itype'] == 'wmf') {
					$outstring = sprintf('q ' . $tr . $tr2 . '%.3F 0 0 %.3F %.3F %.3F cm /FO%d Do Q', $sx, -$sy, $objattr['INNER-X'] * Mpdf::SCALE - $sx * $objattr['wmf_x'], (($this->h - $objattr['INNER-Y']) * Mpdf::SCALE) + $sy * $objattr['wmf_y'], $objattr['ID']); // mPDF 5.7.3 TRANSFORMS
				} else { 				/* -- END IMAGES-WMF -- */
					if (isset($objattr['itype']) && $objattr['itype'] == 'svg') {
						$outstring = sprintf('q ' . $tr . $tr2 . '%.3F 0 0 %.3F %.3F %.3F cm /FO%d Do Q', $sx, -$sy, $objattr['INNER-X'] * Mpdf::SCALE - $sx * $objattr['wmf_x'], (($this->h - $objattr['INNER-Y']) * Mpdf::SCALE) + $sy * $objattr['wmf_y'], $objattr['ID']); // mPDF 5.7.3 TRANSFORMS
					} else {
						$outstring = sprintf("q " . $tr . $tr2 . "%.3F 0 0 %.3F %.3F %.3F cm " . $gradmask . "/I%d Do Q", $obiw * Mpdf::SCALE, $obih * Mpdf::SCALE, $objattr['INNER-X'] * Mpdf::SCALE, ($this->h - ($objattr['INNER-Y'] + $obih )) * Mpdf::SCALE, $objattr['ID']); // mPDF 5.7.3 TRANSFORMS
					}
				}
				$this->writer->write($outstring);
				// LINK
				if (isset($objattr['link'])) {
					$this->Link($objattr['INNER-X'], $objattr['INNER-Y'], $objattr['INNER-WIDTH'], $objattr['INNER-HEIGHT'], $objattr['link']);
				}
				if (isset($objattr['opacity'])) {
					$this->SetAlpha(1);
				}

				// mPDF 5.7.3 TRANSFORMS
				// Transform also affects image borders
				if ($tr2) {
					$this->writer->write('q ' . $tr2 . ' ');
				}
				if ((isset($objattr['border_top']) && $objattr['border_top'] > 0) || (isset($objattr['border_left']) && $objattr['border_left'] > 0) || (isset($objattr['border_right']) && $objattr['border_right'] > 0) || (isset($objattr['border_bottom']) && $objattr['border_bottom'] > 0)) {
					$this->PaintImgBorder($objattr, $is_table);
				}
				if ($tr2) {
					$this->writer->write('Q');
				}

				if (isset($objattr['visibility']) && $objattr['visibility'] != 'visible' && $objattr['visibility']) {
					$this->SetVisibility('visible');
				}
				if (isset($objattr['z-index']) && $objattr['z-index'] > 0 && $this->current_layer == 0) {
					$this->EndLayer();
				}
				// mPDF 5.7.3 TRANSFORMS
				if (isset($objattr['transform'])) {
					$this->writer->write("\n" . '% ETR'); // End Transform
				}
			}

			if ($objattr['type'] === 'barcode') {

				$bgcol = $this->colorConverter->convert(255, $this->PDFAXwarnings);

				if (isset($objattr['bgcolor']) && $objattr['bgcolor']) {
					$bgcol = $objattr['bgcolor'];
				}

				$col = $this->colorConverter->convert(0, $this->PDFAXwarnings);

				if (isset($objattr['color']) && $objattr['color']) {
					$col = $objattr['color'];
				}

				$this->SetFColor($bgcol);
				$this->Rect($objattr['BORDER-X'], $objattr['BORDER-Y'], $objattr['BORDER-WIDTH'], $objattr['BORDER-HEIGHT'], 'F');
				$this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings));

				if (isset($objattr['BORDER-WIDTH'])) {
					$this->PaintImgBorder($objattr, $is_table);
				}

				$barcodeTypes = ['EAN13', 'ISBN', 'ISSN', 'UPCA', 'UPCE', 'EAN8'];
				if (in_array($objattr['btype'], $barcodeTypes, true)) {

					$this->WriteBarcode(
						$objattr['code'],
						$objattr['showtext'],
						$objattr['INNER-X'],
						$objattr['INNER-Y'],
						$objattr['bsize'],
						0,
						0,
						0,
						0,
						0,
						$objattr['bheight'],
						$bgcol,
						$col,
						$objattr['btype'],
						$objattr['bsupp'],
						(isset($objattr['bsupp_code']) ? $objattr['bsupp_code'] : ''),
						$k
					);

				} elseif ($objattr['btype'] === 'QR') {

					if (!class_exists('Mpdf\QrCode\QrCode')) {
						throw new \Mpdf\MpdfException('Class Mpdf\QrCode\QrCode does not exists. Install the package from Packagist with "composer require mpdf/qrcode"');
					}

					$barcodeContent = str_replace('\r\n', "\r\n", $objattr['code']);
					$barcodeContent = str_replace('\n', "\n", $barcodeContent);

					$qrcode = new QrCode\QrCode($barcodeContent, $objattr['errorlevel']);
					if ($objattr['disableborder']) {
						$qrcode->disableBorder();
					}

					$bgColor = [255, 255, 255];
					if ($objattr['bgcolor']) {
						$bgColor = array_map(
							function ($col) {
								return intval(255 * floatval($col));
							},
							explode(" ", $this->SetColor($objattr['bgcolor'], 'CodeOnly'))
						);
					}
					$color = [0, 0, 0];
					if ($objattr['color']) {
						$color = array_map(
							function ($col) {
								return intval(255 * floatval($col));
							},
							explode(" ", $this->SetColor($objattr['color'], 'CodeOnly'))
						);
					}

					$out = new QrCode\Output\Mpdf();
					$out->output(
						$qrcode,
						$this,
						$objattr['INNER-X'],
						$objattr['INNER-Y'],
						$objattr['bsize'] * 25,
						$bgColor,
						$color
					);

					unset($qrcode);

				} else {

					$this->WriteBarcode2(
						$objattr['code'],
						$objattr['INNER-X'],
						$objattr['INNER-Y'],
						$objattr['bsize'],
						$objattr['bheight'],
						$bgcol,
						$col,
						$objattr['btype'],
						$objattr['pr_ratio'],
						$k
					);

				}
			}

			// TEXT CIRCLE
			if ($objattr['type'] == 'textcircle') {
				$bgcol = '';
				if (isset($objattr['bgcolor']) && $objattr['bgcolor']) {
					$bgcol = $objattr['bgcolor'];
				}
				$col = $this->colorConverter->convert(0, $this->PDFAXwarnings);
				if (isset($objattr['color']) && $objattr['color']) {
					$col = $objattr['color'];
				}
				$this->SetTColor($col);
				$this->SetFColor($bgcol);
				if ($bgcol) {
					$this->Rect($objattr['BORDER-X'], $objattr['BORDER-Y'], $objattr['BORDER-WIDTH'], $objattr['BORDER-HEIGHT'], 'F');
				}
				$this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings));
				if (isset($objattr['BORDER-WIDTH'])) {
					$this->PaintImgBorder($objattr, $is_table);
				}
				if (empty($this->directWrite)) {
					$this->directWrite = new DirectWrite($this, $this->otl, $this->sizeConverter, $this->colorConverter);
				}
				if (isset($objattr['top-text'])) {
					$this->directWrite->CircularText($objattr['INNER-X'] + $objattr['INNER-WIDTH'] / 2, $objattr['INNER-Y'] + $objattr['INNER-HEIGHT'] / 2, $objattr['r'] / $k, $objattr['top-text'], 'top', $objattr['fontfamily'], $objattr['fontsize'] / $k, $objattr['fontstyle'], $objattr['space-width'], $objattr['char-width'], (isset($objattr['divider']) ? $objattr['divider'] : ''));
				}
				if (isset($objattr['bottom-text'])) {
					$this->directWrite->CircularText($objattr['INNER-X'] + $objattr['INNER-WIDTH'] / 2, $objattr['INNER-Y'] + $objattr['INNER-HEIGHT'] / 2, $objattr['r'] / $k, $objattr['bottom-text'], 'bottom', $objattr['fontfamily'], $objattr['fontsize'] / $k, $objattr['fontstyle'], $objattr['space-width'], $objattr['char-width'], (isset($objattr['divider']) ? $objattr['divider'] : ''));
				}
			}

			$this->ResetSpacing();

			// LIST MARKERS (Text or bullets)	// mPDF 6  Lists
			if ($objattr['type'] == 'listmarker') {
				if (isset($objattr['fontfamily'])) {
					$this->SetFont($objattr['fontfamily'], $objattr['fontstyle'], $objattr['fontsizept']);
				}
				$col = $this->colorConverter->convert(0, $this->PDFAXwarnings);
				if (isset($objattr['colorarray']) && ($objattr['colorarray'])) {
					$col = $objattr['colorarray'];
				}

				if (isset($objattr['bullet']) && $objattr['bullet']) { // Used for position "outside" only
					$type = $objattr['bullet'];
					$size = $objattr['size'];

					if ($objattr['listmarkerposition'] == 'inside') {
						$adjx = $size / 2;
						if ($objattr['dir'] == 'rtl') {
							$adjx += $objattr['offset'];
						}
						$this->x += $adjx;
					} else {
						$adjx = $objattr['offset'];
						$adjx += $size / 2;
						if ($objattr['dir'] == 'rtl') {
							$this->x += $adjx;
						} else {
							$this->x -= $adjx;
						}
					}

					$yadj = $objattr['lineBox']['glyphYorigin'];
					if (isset($this->CurrentFont['desc']['XHeight']) && $this->CurrentFont['desc']['XHeight']) {
						$xh = $this->CurrentFont['desc']['XHeight'];
					} else {
						$xh = 500;
					}
					$yadj -= ($this->FontSize * $xh / 1000) * 0.625; // Vertical height of bullet (centre) from baseline= XHeight * 0.625
					$this->y += $yadj;

					$this->_printListBullet($this->x, $this->y, $size, $type, $col);
				} else {
					$this->SetTColor($col);
					$w = $this->GetStringWidth($texto);
					// NB If change marker-offset, also need to alter in function _getListMarkerWidth
					$adjx = $this->sizeConverter->convert($this->list_marker_offset, $this->FontSize);
					if ($objattr['dir'] == 'rtl') {
						$align = 'L';
						$this->x += $adjx;
					} else {
						// Use these lines to set as marker-offset, right-aligned - default
						$align = 'R';
						$this->x -= $adjx;
						$this->x -= $w;
					}
					$this->Cell($w, $this->FontSize, $texto, 0, 0, $align, 0, '', 0, 0, 0, 'T', 0, false, false, 0, $objattr['lineBox']);
					$this->SetTColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
				}
			}

			// DOT-TAB
			if ($objattr['type'] == 'dottab') {
				if (isset($objattr['fontfamily'])) {
					$this->SetFont($objattr['fontfamily'], '', $objattr['fontsize']);
				}
				$sp = $this->GetStringWidth(' ');
				$nb = floor(($w - 2 * $sp) / $this->GetStringWidth('.'));
				if ($nb > 0) {
					$dots = ' ' . str_repeat('.', $nb) . ' ';
				} else {
					$dots = ' ';
				}
				$col = $this->colorConverter->convert(0, $this->PDFAXwarnings);
				if (isset($objattr['colorarray']) && ($objattr['colorarray'])) {
					$col = $objattr['colorarray'];
				}
				$this->SetTColor($col);
				$save_dh = $this->divheight;
				$save_sbd = $this->spanborddet;
				$save_textvar = $this->textvar; // mPDF 5.7.1
				$this->spanborddet = '';
				$this->divheight = 0;
				$this->textvar = 0x00; // mPDF 5.7.1

				$this->Cell($w, $h, $dots, 0, 0, 'C', 0, '', 0, 0, 0, 'T', 0, false, false, 0, $objattr['lineBox']); // mPDF 6 DOTTAB
				$this->spanborddet = $save_sbd;
				$this->textvar = $save_textvar; // mPDF 5.7.1
				$this->divheight = $save_dh;
				$this->SetTColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
			}

			/* -- FORMS -- */
			// TEXT/PASSWORD INPUT
			if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'TEXT' || $objattr['subtype'] == 'PASSWORD')) {
				$this->form->print_ob_text($objattr, $w, $h, $texto, $rtlalign, $k, $blockdir);
			}

			// TEXTAREA
			if ($objattr['type'] == 'textarea') {
				$this->form->print_ob_textarea($objattr, $w, $h, $texto, $rtlalign, $k, $blockdir);
			}

			// SELECT
			if ($objattr['type'] == 'select') {
				$this->form->print_ob_select($objattr, $w, $h, $texto, $rtlalign, $k, $blockdir);
			}


			// INPUT/BUTTON as IMAGE
			if ($objattr['type'] == 'input' && $objattr['subtype'] == 'IMAGE') {
				$this->form->print_ob_imageinput($objattr, $w, $h, $texto, $rtlalign, $k, $blockdir, $is_table);
			}

			// BUTTON
			if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'SUBMIT' || $objattr['subtype'] == 'RESET' || $objattr['subtype'] == 'BUTTON')) {
				$this->form->print_ob_button($objattr, $w, $h, $texto, $rtlalign, $k, $blockdir);
			}

			// CHECKBOX
			if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'CHECKBOX')) {
				$this->form->print_ob_checkbox($objattr, $w, $h, $texto, $rtlalign, $k, $blockdir, $x, $y);
			}
			// RADIO
			if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'RADIO')) {
				$this->form->print_ob_radio($objattr, $w, $h, $texto, $rtlalign, $k, $blockdir, $x, $y);
			}
			/* -- END FORMS -- */
		}

		$this->SetFont($save_currentfontfamily, $save_currentfontstyle, $save_currentfontsize);

		$this->y = $save_y;
		$this->x = $save_x;

		unset($content);
	}

	function _printListBullet($x, $y, $size, $type, $color)
	{
		// x and y are the centre of the bullet; size is the width and/or height in mm
		$fcol = $this->SetTColor($color, true);
		$lcol = strtoupper($fcol); // change 0 0 0 rg to 0 0 0 RG
		$this->writer->write(sprintf('q %s %s', $lcol, $fcol));
		$this->writer->write('0 j 0 J [] 0 d');
		if ($type == 'square') {
			$size *= 0.85; // Smaller to appear the same size as circle/disc
			$this->writer->write(sprintf('%.3F %.3F %.3F %.3F re f', ($x - $size / 2) * Mpdf::SCALE, ($this->h - $y + $size / 2) * Mpdf::SCALE, ($size) * Mpdf::SCALE, (-$size) * Mpdf::SCALE));
		} elseif ($type == 'disc') {
			$this->Circle($x, $y, $size / 2, 'F'); // Fill
		} elseif ($type == 'circle') {
			$lw = $size / 12; // Line width
			$this->writer->write(sprintf('%.3F w ', $lw * Mpdf::SCALE));
			$this->Circle($x, $y, $size / 2 - $lw / 2, 'S'); // Stroke
		}
		$this->writer->write('Q');
	}

	// mPDF 6
	// Get previous character and move pointers
	function _moveToPrevChar(&$contentctr, &$charctr, $content)
	{
		$lastchar = false;
		$charctr--;
		while ($charctr < 0) { // go back to previous $content[]
			$contentctr--;
			if ($contentctr < 0) {
				return false;
			}
			if ($this->usingCoreFont) {
				$charctr = strlen($content[$contentctr]) - 1;
			} else {
				$charctr = mb_strlen($content[$contentctr], $this->mb_enc) - 1;
			}
		}
		if ($this->usingCoreFont) {
			$lastchar = $content[$contentctr][$charctr];
		} else {
			$lastchar = mb_substr($content[$contentctr], $charctr, 1, $this->mb_enc);
		}
		return $lastchar;
	}

	// Get previous character
	function _getPrevChar($contentctr, $charctr, $content)
	{
		$lastchar = false;
		$charctr--;
		while ($charctr < 0) { // go back to previous $content[]
			$contentctr--;
			if ($contentctr < 0) {
				return false;
			}
			if ($this->usingCoreFont) {
				$charctr = strlen($content[$contentctr]) - 1;
			} else {
				$charctr = mb_strlen($content[$contentctr], $this->mb_enc) - 1;
			}
		}
		if ($this->usingCoreFont) {
			$lastchar = $content[$contentctr][$charctr];
		} else {
			$lastchar = mb_substr($content[$contentctr], $charctr, 1, $this->mb_enc);
		}
		return $lastchar;
	}

	function WriteFlowingBlock($s, $sOTLdata)
	{
	// mPDF 5.7.1
		$currentx = $this->x;
		$is_table = $this->flowingBlockAttr['is_table'];
		$table_draft = $this->flowingBlockAttr['table_draft'];
		// width of all the content so far in points
		$contentWidth = & $this->flowingBlockAttr['contentWidth'];
		// cell width in points
		$maxWidth = & $this->flowingBlockAttr['width'];
		$lineCount = & $this->flowingBlockAttr['lineCount'];
		// line height in user units
		$stackHeight = & $this->flowingBlockAttr['height'];
		$align = & $this->flowingBlockAttr['align'];
		$content = & $this->flowingBlockAttr['content'];
		$contentB = & $this->flowingBlockAttr['contentB'];
		$font = & $this->flowingBlockAttr['font'];
		$valign = & $this->flowingBlockAttr['valign'];
		$blockstate = $this->flowingBlockAttr['blockstate'];
		$cOTLdata = & $this->flowingBlockAttr['cOTLdata']; // mPDF 5.7.1

		$newblock = $this->flowingBlockAttr['newblock'];
		$blockdir = $this->flowingBlockAttr['blockdir'];

		// *********** BLOCK BACKGROUND COLOR ***************** //
		if ($this->blk[$this->blklvl]['bgcolor'] && !$is_table) {
			$fill = 0;
		} else {
			$this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings));
			$fill = 0;
		}
		$font[] = $this->saveFont();
		$content[] = '';
		$contentB[] = '';
		$cOTLdata[] = $sOTLdata; // mPDF 5.7.1
		$currContent = & $content[count($content) - 1];

		$CJKoverflow = false;
		$Oikomi = false; // mPDF 6
		$hanger = '';

		// COLS
		$oldcolumn = $this->CurrCol;
		if ($this->ColActive && !$is_table) {
			$this->breakpoints[$this->CurrCol][] = $this->y;
		} // *COLUMNS*

		/* -- TABLES -- */
		if ($is_table) {
			$ipaddingL = 0;
			$ipaddingR = 0;
			$paddingL = 0;
			$paddingR = 0;
			$cpaddingadjustL = 0;
			$cpaddingadjustR = 0;
			// Added mPDF 3.0
			$fpaddingR = 0;
			$fpaddingL = 0;
		} else {
			/* -- END TABLES -- */
			$ipaddingL = $this->blk[$this->blklvl]['padding_left'];
			$ipaddingR = $this->blk[$this->blklvl]['padding_right'];
			$paddingL = ($ipaddingL * Mpdf::SCALE);
			$paddingR = ($ipaddingR * Mpdf::SCALE);
			$this->cMarginL = $this->blk[$this->blklvl]['border_left']['w'];
			$cpaddingadjustL = -$this->cMarginL;
			$this->cMarginR = $this->blk[$this->blklvl]['border_right']['w'];
			$cpaddingadjustR = -$this->cMarginR;
			// Added mPDF 3.0 Float DIV
			$fpaddingR = 0;
			$fpaddingL = 0;
			/* -- CSS-FLOAT -- */
			if (count($this->floatDivs)) {
				list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
				if ($r_exists) {
					$fpaddingR = $r_width;
				}
				if ($l_exists) {
					$fpaddingL = $l_width;
				}
			}
			/* -- END CSS-FLOAT -- */

			$usey = $this->y + 0.002;
			if (($newblock) && ($blockstate == 1 || $blockstate == 3) && ($lineCount == 0)) {
				$usey += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
			}
			/* -- CSS-IMAGE-FLOAT -- */
			// If float exists at this level
			if (isset($this->floatmargins['R']) && $usey <= $this->floatmargins['R']['y1'] && $usey >= $this->floatmargins['R']['y0'] && !$this->floatmargins['R']['skipline']) {
				$fpaddingR += $this->floatmargins['R']['w'];
			}
			if (isset($this->floatmargins['L']) && $usey <= $this->floatmargins['L']['y1'] && $usey >= $this->floatmargins['L']['y0'] && !$this->floatmargins['L']['skipline']) {
				$fpaddingL += $this->floatmargins['L']['w'];
			}
			/* -- END CSS-IMAGE-FLOAT -- */
		} // *TABLES*
		// OBJECTS - IMAGES & FORM Elements (NB has already skipped line/page if required - in printbuffer)
		if (substr($s, 0, 3) == "\xbb\xa4\xac") { // identifier has been identified!
			$objattr = $this->_getObjAttr($s);
			$h_corr = 0;
			if ($is_table) { // *TABLES*
				$maximumW = ($maxWidth / Mpdf::SCALE) - ($this->cellPaddingL + $this->cMarginL + $this->cellPaddingR + $this->cMarginR);  // *TABLES*
			} // *TABLES*
			else { // *TABLES*
				if (($newblock) && ($blockstate == 1 || $blockstate == 3) && ($lineCount == 0) && (!$is_table)) {
					$h_corr = $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
				}
				$maximumW = ($maxWidth / Mpdf::SCALE) - ($this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_right'] + $this->blk[$this->blklvl]['border_right']['w'] + $fpaddingL + $fpaddingR );
			} // *TABLES*
			$objattr = $this->inlineObject($objattr['type'], $this->lMargin + $fpaddingL + ($contentWidth / Mpdf::SCALE), ($this->y + $h_corr), $objattr, $this->lMargin, ($contentWidth / Mpdf::SCALE), $maximumW, $stackHeight, true, $is_table);

			// SET LINEHEIGHT for this line ================ RESET AT END
			$stackHeight = max($stackHeight, $objattr['OUTER-HEIGHT']);
			$this->objectbuffer[count($content) - 1] = $objattr;
			// if (isset($objattr['vertical-align'])) { $valign = $objattr['vertical-align']; }
			// else { $valign = ''; }
			// LIST MARKERS	// mPDF 6  Lists
			if ($objattr['type'] == 'image' && isset($objattr['listmarker']) && $objattr['listmarker'] && $objattr['listmarkerposition'] == 'outside') {
				// do nothing
			} else {
				$contentWidth += ($objattr['OUTER-WIDTH'] * Mpdf::SCALE);
			}
			return;
		}

		$lbw = $rbw = 0; // Border widths
		if (!empty($this->spanborddet)) {
			if (isset($this->spanborddet['L'])) {
				$lbw = $this->spanborddet['L']['w'];
			}
			if (isset($this->spanborddet['R'])) {
				$rbw = $this->spanborddet['R']['w'];
			}
		}

		if ($this->usingCoreFont) {
			$clen = strlen($s);
		} else {
			$clen = mb_strlen($s, $this->mb_enc);
		}

		// for every character in the string
		for ($i = 0; $i < $clen; $i++) {
			// extract the current character
			// get the width of the character in points
			if ($this->usingCoreFont) {
				$c = $s[$i];
				// Soft Hyphens chr(173)
				$cw = ($this->GetCharWidthCore($c) * Mpdf::SCALE);
				if (($this->textvar & TextVars::FC_KERNING) && $i > 0) { // mPDF 5.7.1
					if (isset($this->CurrentFont['kerninfo'][$s[($i - 1)]][$c])) {
						$cw += ($this->CurrentFont['kerninfo'][$s[($i - 1)]][$c] * $this->FontSizePt / 1000 );
					}
				}
			} else {
				$c = mb_substr($s, $i, 1, $this->mb_enc);
				$cw = ($this->GetCharWidthNonCore($c, false) * Mpdf::SCALE);
				// mPDF 5.7.1
				// Use OTL GPOS
				if (isset($this->CurrentFont['useOTL']) && ($this->CurrentFont['useOTL'] & 0xFF)) {
					// ...WriteFlowingBlock...
					// Only  add XAdvanceL (not sure at present whether RTL or LTR writing direction)
					// At this point, XAdvanceL and XAdvanceR will balance
					if (isset($sOTLdata['GPOSinfo'][$i]['XAdvanceL'])) {
						$cw += $sOTLdata['GPOSinfo'][$i]['XAdvanceL'] * (1000 / $this->CurrentFont['unitsPerEm']) * ($this->FontSize / 1000) * Mpdf::SCALE;
					}
				}
				if (($this->textvar & TextVars::FC_KERNING) && $i > 0) { // mPDF 5.7.1
					$lastc = mb_substr($s, ($i - 1), 1, $this->mb_enc);
					$ulastc = $this->UTF8StringToArray($lastc, false);
					$uc = $this->UTF8StringToArray($c, false);
					if (isset($this->CurrentFont['kerninfo'][$ulastc[0]][$uc[0]])) {
						$cw += ($this->CurrentFont['kerninfo'][$ulastc[0]][$uc[0]] * $this->FontSizePt / 1000 );
					}
				}
			}

			if ($i == 0) {
				$cw += $lbw * Mpdf::SCALE;
				$contentB[(count($contentB) - 1)] .= 'L';
			}
			if ($i == ($clen - 1)) {
				$cw += $rbw * Mpdf::SCALE;
				$contentB[(count($contentB) - 1)] .= 'R';
			}
			if ($c == ' ') {
				$currContent .= $c;
				$contentWidth += $cw;
				continue;
			}

			// Paragraph INDENT
			$WidthCorrection = 0;
			if (($newblock) && ($blockstate == 1 || $blo