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

require_once('OFC/Charts/OFC_Charts_Scatter.php');

class OFC_Charts_Scatter_Line extends OFC_Charts_Scatter
{
   function OFC_Charts_Scatter_Line( $colour, $dot_size )
	{
		parent::OFC_Charts_Scatter( $colour, $dot_size );

		$this->type  = 'scatter_line';
	}

	function set_default_dot_style( $style )
	{
		$tmp = 'dot-style';
		$this->$tmp = $style;
	}

	function set_colour( $colour )
	{
		$this->colour = $colour;
	}

	function set_width( $width )
	{
		$this->width = $width;
	}

	function set_values( $values )
	{
		$this->values = $values;
	}

	function set_step_horizontal()
	{
		$this->stepgraph = 'horizontal';
	}

	function set_step_vertical()
	{
		$this->stepgraph = 'vertical';
	}

	function set_key( $text, $font_size )
	{
		$this->text      = $text;
		$tmp = 'font-size';
		$this->$tmp = $font_size;
	}
}