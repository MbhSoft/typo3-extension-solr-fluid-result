<?php

namespace MbhSoftware\SolrFluidResult\Domain\Model;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Domain\Model\Category;

class CategoryFilterItem extends AbstractEntity
{

    public const TYPE_OPERATOR = 0;
    public const TYPE_CATEGORY = 1;
    public const TYPE_QUERY = 2;

    public const OPERATOR_AND = 0;
    public const OPERATOR_OR = 1;

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
     * @var string
     */
    protected $query;

    /**
     * @var ObjectStorage<Category>
     */
    protected $categories;

    /**
     * @var ObjectStorage<\MbhSoftware\SolrFluidResult\Domain\Model\CategoryFilterItem>
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
        $this->categories = new ObjectStorage();
        $this->items = new ObjectStorage();
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
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    public function setQuery(string $query): void
    {
        $this->query = $query;
    }

    /**
     * Get categories
     *
     * @return ObjectStorage<Category>
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set categories
     *
     * @param ObjectStorage $categories
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
     * @return ObjectStorage
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param ObjectStorage $items
     */
    public function setItems(ObjectStorage $items)
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
            'operator' => $this->operator,
        ];
        if ($this->type == self::TYPE_OPERATOR) {
            foreach ($this->items as $item) {
                $flat['items'][] = $item->flatten();
            }
        } elseif ($this->type == self::TYPE_CATEGORY) {
            foreach ($this->categories as $category) {
                $flat['categories'][] = $category->getTitle();
            }
        } elseif ($this->type == self::TYPE_QUERY) {
            $flat['query'] = $this->query;
        }
        return $flat;
    }
}
