<?php
/**
 * Pluralize functions.
 */

namespace Acela\Core;


function wordGetPluralRules()
{
	$rules = [
		'unpluralizable' => [
			'advice',
			'aircraft',
			'barracks',
			'cattle',
			'congratulations',
			'deer',
			'fish',
			'headquarters',
			'information',
			'luggage',
			'news',
			'pyjamas',
			'scissors',
			'sheep',
			'species',
			'trousers',
		],
		'irregularForms' => [
			'alumnus'		=> 'alumni',
			'appendix'		=> 'appendices',
			'belief'		=> 'beliefs',
			'brief'			=> 'briefs',
			'cactus'		=> 'cacti',
			'chef'			=> 'chefs',
			'child'			=> 'children',
			'criterion'		=> 'criteria',
			'focus'			=> 'foci',
			'foot'			=> 'feet',
			'fungus'		=> 'fungi',
			'goose'			=> 'geese',
			'index'			=> 'indices',
			'man'			=> 'men',
			'mouse'			=> 'mice',
			'nucleus'		=> 'nuclei',
			'ox'			=> 'oxen',
			'person'		=> 'people',
			'phenomenon'	=> 'phenomena',
			'photo'			=> 'photos',
			'piano'			=> 'piano',
			'portico'		=> 'porticos',
			'proof'			=> 'proofs',
			'radius'		=> 'radii',
			'roof'			=> 'roofs',
			'syllabus'		=> 'syllabi',
			'tooth'			=> 'teeth',
			'woman'			=> 'women',
		],
	];
	
	return $rules;
}

/**
 * Pluralize a word.
 * 
 * @param string $word A word to be pluralized.
 * @return string The pluralized form of the word.
 */
function wordPluralize($word)
{
	$rules = wordGetPluralRules();
	
	/**
	 * Rule - Some words either have identical singular and plural forms, or are
	 * uncountable.
	 */
	if(in_array($word, $rules['unpluralizable']))
	{
		// Do nothing.
	}
	/**
	 * Rule - Some words have non-standard plurals.
	 */
	elseif(!empty($rules['irregularForms'][$word]))
	{
		$word = $rules['irregularForms'][$word];
	}
	/**
	 * Rule - Words ending in z get "zes" added.
	 */
	elseif(substr($word, -1) === 'z')
	{
		$word .= 'zes';
	}
	/**
	 * Rule - Words ending in o preceded by a consonant have "es" added.
	 */
	elseif(preg_match('/([b-df-hj-np-tv-z]o)$/', $word))
	{
		$word .= 'es';
	}
	/**
	 * Rule - Words ending in -is should end in "es" instead.
	 */
	elseif(substr($word, -2) === 'is')
	{
		$word = substr($word, 0, -2).'es';
	}
	/**
	 * Rule - Words ending in -ch, -sh, x, s or s-like sounds get "es" at the end.
	 */
	elseif(
		substr($word, -2) === 'ch'
		or substr($word, -2) === 'sh'
		or substr($word, -1) === 'x'
		or substr($word, -1) === 's'
	)
	{
		$word .= 'es';
	}
	/**
	 * Rule - Words ending in y have the y dropped and "ies" added.
	 */
	elseif(substr($word, -1) === 'y')
	{
		$word = substr($word, 0, -1).'ies';
	}
	/**
	 * Rule - Words ending in -f or -fe end in "ves" added.
	 */
	elseif(substr($word, -1) === 'f')
	{
		$word = substr($word, 0, -1).'ves';
	}
	elseif(substr($word, -2) === 'fe')
	{
		$word = substr($word, 0, -2).'ves';
	}
	/**
	 * Rule - Default is to simply add "s" to a word.
	 */
	else
	{
		$word .= 's';
	}
	
	return $word;
}

/**
 * Pluralize a word.
 * 
 * @param string $word A word to be pluralized.
 * @return string The pluralized form of the word.
 */
function wordSingularize($word)
{
	$rules = wordGetPluralRules();
	$rules['irregularForms'] = array_flip($rules['irregularForms']);
		
	/**
	 * Rule - Some words either have identical singular and plural forms, or are
	 * uncountable.
	 */
	if(in_array($word, $rules['unpluralizable']))
	{
		// Do nothing.
	}
	/**
	 * Rule - Some words have non-standard plurals.
	 */
	elseif(!empty($rules['irregularForms'][$word]))
	{
		$word = $rules['irregularForms'][$word];
	}
	/**
	 * Rule - Words ending in -zzes should have "zes" removed.
	 */
	elseif(substr($word, -4) === 'zzes')
	{
		$word = substr($word, 0, -3);
	}
	/**
	 * Rule - Words ending in -oes should have "es" removed.
	 */
	elseif(substr($word, -3) === 'oes')
	{
		$word = substr($word, 0, -2);
	}
	/**
	 * Rule - Words ending in -ches, -shes, xes, ses or s-like sounds followed by es, have "es" remove.
	 */
	elseif(
		substr($word, -4) === 'ches'
		or substr($word, -4) === 'shes'
		or substr($word, -3) === 'xes'
		or substr($word, -3) === 'ses'
	)
	{
		$word = substr($word, 0, -2);
	}
	/**
	 * Rule - Words ending in -es should end in "is" instead.
	 */
	elseif(substr($word, -2) === 'es')
	{
		$word = substr($word, 0, -2).'is';
	}
	/**
	 * Rule - Words ending in -ies should end in "y" instead.
	 */
	elseif(substr($word, -3) === 'ies')
	{
		$word = substr($word, 0, -3).'y';
	}
	/**
	 * Rule - Words ending in -ves should end in "f" instead.
	 */
	elseif(substr($word, -3) === 'ves')
	{
		$word = substr($word, 0, -3).'f';
	}
	/**
	 * Rule - Default is to remove "s" from the end of a word.
	 */
	else
	{
		$word = substr($word, 0, -1);
	}
	
	return $word;
}