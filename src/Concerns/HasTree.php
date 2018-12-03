<?php


namespace SimpelDigitaal\TreeModel\Concerns;

trait HasTree
{
    use HasTreeNames, HasTreeAttributes, BuildsTree;
}