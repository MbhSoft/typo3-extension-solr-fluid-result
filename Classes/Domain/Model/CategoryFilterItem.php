<?php

namespace MbhSoftware\SolrFluidResult\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Domain\Model\Category;

class CategoryFilterItem extends AbstractEntity
{

    const TYPE_OPERATOR = 0;
    const TYPE_CATEGORY = 1;

    const OPERATOR_AND = 0;
    const OPERATOR_OR = 1;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var int
     */
    protected $type;

    /**
     * @var int
     */
    protected $operator;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     */
    protected $categories;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\MbhSoftware\SolrFluidResult\Domain\Model\CategoryFilterItem>
     */
    protected $items;

    /**
     * @var \MbhSoftware\SolrFluidResult\Domain\Model\CategoryFilterItem|null
     */
    protected $parent = null;

    /**
     * Initialize categories and media relation
     *
     */
    public function __construct()
    {
        $this->categories = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->items = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param int $operator
     */
    public function setOperator(int $operator)
    {
        $this->operator = $operator;
    }

    /**
     * Get categories
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set categories
     *
     * @param  \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * Adds a category to this categories.
     *
     * @param Category $category
     */
    public function addCategory(Category $category)
    {
        $this->categories->attach($category);
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $items
     */
    public function setItems(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $items)
    {
        $this->items = $items;
    }

    /**
     * @return \MbhSoftware\SolrFluidResult\Domain\Model\CategoryFilterItem|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param \MbhSoftware\SolrFluidResult\Domain\Model\CategoryFilterItem|null $parent
     */
    public function setParent(\MbhSoftware\SolrFluidResult\Domain\Model\CategoryFilterItem $parent)
    {
        $this->parent = $parent;
    }

    public function flatten()
    {
        $flat = [
            'type' => $this->type,
            'operator' => $this->operator
        ];
        if ($this->type == self::TYPE_OPERATOR) {
            foreach ($this->items as $item) {
                $flat['items'][] = $item->flatten();
            }
        } elseif ($this->type == self::TYPE_CATEGORY) {
            foreach ($this->categories as $category) {
                $flat['categories'][] = $category->getTitle();
            }
        }
        return $flat;
    }
}
