<?php
/**
 * PHP Integration of Open Flash Chart
 * Copyright (C) 2008 John Glazebrook <open-flash-chart@teethgrinder.co.uk>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */

class OFC_Charts_Base
{
    function OFC_Charts_Base()
    {
    }
}

/**
 * A private class. All the other line-dots inherit from this.
 * Gives them all some common methods.
 */
class OFC_Charts_Dot_Base
{
	/**
	 * @param $type string
	 * @param $value integer
	 */
	function OFC_Charts_Dot_Base($type, $value=null)
	{
		$this->type = $type;
		if( isset( $value ) )
			$this->value( $value );
	}

	/**
	 * For line charts that only require a Y position
	 * for each point.
	 * @param $value as integer, the Y position
	 */
	function value( $value )
	{
		$this->value = $value;
	}

	/**
	 * For scatter charts that require an X and Y position for
	 * each point.
	 *
	 * @param $x as integer
	 * @param $y as integer
	 */
	function position( $x, $y )
	{
		$this->x = $x;
		$this->y = $y;
	}

	/**
	 * @param $colour is a string, HEX colour, e.g. '#FF0000' red
	 */
	function colour($colour)
	{
		$this->colour = $colour;
		return $this;
	}

	/**
	 * The tooltip for this dot.
	 */
	function tooltip( $tip )
	{
		$this->tip = $tip;
		return $this;
	}

	/**
	 * @param $size is an integer. Size of the dot.
	 */
	function size($size)
	{
		$tmp = 'dot-size';
		$this->$tmp = $size;
		return $this;
	}

	/**
	 * a private method
	 */
	function type( $type )
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @param $size is an integer. The size of the hollow 'halo' around the dot that masks the line.
	 */
	function halo_size( $size )
	{
		$tmp = 'halo-size';
		$this->$tmp = $size;
		return $this;
	}

	/**
	 * @param $do as string. One of three options (examples):
	 *  - "http://example.com" - browse to this URL
	 *  - "https://example.com" - browse to this URL
	 *  - "trace:message" - print this message in the FlashDevelop debug pane
	 *  - all other strings will be called as Javascript functions, so a string "hello_world"
	 *  will call the JS function "hello_world(index)". It passes in the index of the
	 *  point.
	 */
	function on_click( $do )
	{
		$tmp = 'on-click';
		$this->$tmp = $do;
	}
}

/**
 * Draw a hollow dot
 */
class OFC_Charts_Hollow_Dot extends OFC_Charts_Dot_Base
{
	function OFC_Charts_Hollow_Dot($value=null)
	{
		parent::OFC_Charts_Dot_Base( 'hollow-dot', $value );
	}
}

/**
 * Draw a star
 */
class OFC_Charts_Star extends OFC_Charts_Dot_Base
{
	/**
	 * The constructor, takes an optional $value
	 */
	function OFC_Charts_Star($value=null)
	{
		parent::OFC_Charts_Dot_Base( 'star', $value );
	}

	/**
	 * @param $angle is an integer.
	 */
	function rotation($angle)
	{
		$this->rotation = $angle;
		return $this;
	}

	/**
	 * @param $is_hollow is a boolean.
	 */
	function hollow($is_hollow)
	{
		$this->hollow = $is_hollow;
	}
}

/**
 * Draw a 'bow tie' shape.
 */
class OFC_Charts_Bow extends OFC_Charts_Dot_Base
{
	/**
	 * The constructor, takes an optional $value
	 */
	function OFC_Charts_Bow($value=null)
	{
		parent::OFC_Charts_Dot_Base( 'bow', $value );
	}

	/**
	 * Rotate the anchor object.
	 * @param $angle is an integer.
	 */
	function rotation($angle)
	{
		$this->rotation = $angle;
		return $this;
	}
}

/**
 * An <i><b>n</b></i> sided shape.
 */
class OFC_Charts_Anchor extends OFC_Charts_Dot_Base
{
	/**
	 * The constructor, takes an optional $value
	 */
	function OFC_Charts_Anchor($value=null)
	{
		parent::OFC_Charts_Dot_Base( 'anchor', $value );
	}

	/**
	 * Rotate the anchor object.
	 * @param $angle is an integer.
	 */
	function rotation($angle)
	{
		$this->rotation = $angle;
		return $this;
	}

	/**
	 * @param $sides is an integer. Number of sides this shape has.
	 */
	function sides($sides)
	{
		$this->sides = $sides;
		return $this;
	}
}

/**
 * A simple dot
 */
class OFC_Charts_Dot extends OFC_Charts_Dot_Base
{
	/**
	 * The constructor, takes an optional $value
	 */
	function OFC_Charts_Dot($value=null)
	{
		parent::OFC_Charts_Dot_Base( 'dot', $value );
	}
}

/**
 * A simple dot
 */
class OFC_Charts_Solid_Dot extends OFC_Charts_Dot_Base
{
	/**
	 * The constructor, takes an optional $value
	 */
	function OFC_Charts_Solid_Dot($value=null)
	{
		parent::OFC_Charts_Dot_Base( 'solid-dot', $value );
	}
}