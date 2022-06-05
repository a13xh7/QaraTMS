/**
 * Tree sortable jQuery library using jQuery UI sortable.
 *
 * @package TreeSortable
 * @license MIT
 * @author Sajeeb Ahamed <sajeeb07ahamed@gmail.com>
 */

const $ = jQuery;

const getCenterPosition = element => {
    const { top, left, width, height } = element.getBoundingClientRect();

    return {
        x: left + width / 2,
        y: top + height / 2,
    };
};

const getDistance = (elementA, elementB) => {
    const positionA = getCenterPosition(elementA);
    const positionB = getCenterPosition(elementB);

    const distanceX = Math.floor(Math.abs(positionA.x - positionB.x));
    const distanceY = Math.floor(Math.abs(positionA.y - positionB.y));

    return { distanceX, distanceY };
};

function TreeSortable() {
    var treeSortable = {
        options: {
            depth: 30,
            treeSelector: "#tree",
            branchSelector: ".tree-branch",
            branchPathSelector: ".branch-path",
            dragHandlerSelector: ".branch-drag-handler",
            placeholderName: "sortable-placeholder",
            childrenBusSelector: ".children-bus",
            levelPrefix: "branch-level",
            maxLevel: 10,
            dataAttributes: {
                id: "id",
                parent: "parent",
                level: "level",
            },
        },
        run() {
            this.jQuerySupplements();
            this.initSorting();
        },

        cleanSelector(selector) {
            return selector.replace(/^[\.#]/g, "");
        },

        getInstance() {
            return this;
        },

        getTreeEdge() {
            return $(treeSortable.options.treeSelector).offset().left;
        },
        pxToNumber(str) {
            return new RegExp("px$", "i").test(str) ? str.slice(0, -2) * 1 : 0;
        },
        numberToPx(num) {
            return `${num}px`;
        },
        onSortCompleted(callback) {
            $(document).on("sortCompleted", callback);
        },

        generateUUID() {
            return "xxxxxxxx-xxx".replace(/[xy]/g, function (c) {
                var r = (Math.random() * 16) | 0,
                    v = c == "x" ? r : (r & 0x3) | 0x8;
                return v.toString(16);
            });
        },
        createBranch({ id, parent_id, title, level }) {
            const {
                options: {
                    branchSelector,
                    branchPathSelector,
                    dragHandlerSelector,
                    childrenBusSelector,
                    levelPrefix,
                    dataAttributes: { idAttr, parentAttr, level: levelAttr },
                },
                cleanSelector,
            } = treeSortable;
            return `
		<li class="${cleanSelector(
            branchSelector
        )} ${levelPrefix}-${level}" data-${idAttr}="${id}" data-${parentAttr}="${parent_id}" data-${levelAttr}="${level}">
            <div class="contents">
                <span class="${cleanSelector(branchPathSelector)}"></span>
                <div class="branch-wrapper">
                    <div class="left-sidebar">
                        <div class="${cleanSelector(dragHandlerSelector)}">
                            <ion-icon name="move-outline"></ion-icon>
                        </div>
                        <span class="branch-title">${title}</span>
                    </div>
                    <div class="right-sidebar">
                        <button type="button" class="button add-child" title="Add a new child">
                            <ion-icon name="person-add-outline"></ion-icon>
                        </button>
                        <button type="button" class="button add-sibling" title="Add a new sibling">
                            <ion-icon name="people-outline"></ion-icon>
                        </button>
                        <button type="button" class="button remove-branch" title="Remove Branch">
                            <ion-icon name="trash-outline"></ion-icon>
                        </button>
                    </div>
                </div>
            </div>
            <div class="${cleanSelector(childrenBusSelector)}"></div>
		</li>
	`;
        },

        jQuerySupplements() {
            const { options } = treeSortable;
            const { levelPrefix, dataAttributes } = options;

            $.fn.extend({
                getBranchLevel() {
                    if ($(this).length === 0) return 0;

                    const { depth } = options;
                    const margin = $(this).css("margin-left");

                    return /(px)|(em)|(rem)$/i.test(margin)
                        ? Math.floor(margin.slice(0, -2) / depth) + 1
                        : Math.floor(margin / depth) + 1;
                },
                updateBranchLevel(current, prev = null) {
                    return this.each(function () {
                        prev = prev || $(this).getBranchLevel() || 1;
                        $(this)
                            .removeClass(levelPrefix + "-" + prev)
                            .addClass(levelPrefix + "-" + current)
                            .data(dataAttributes.level, current)
                            .attr(`data-${dataAttributes.level}`, current);
                    });
                },
                shiftBranchLevel(dx) {
                    return this.each(function () {
                        let level = $(this).getBranchLevel() || 1,
                            newLevel = level + dx;

                        $(this)
                            .removeClass(levelPrefix + "-" + level)
                            .addClass(levelPrefix + "-" + newLevel)
                            .data(dataAttributes.level, newLevel)
                            .attr(`data-${dataAttributes.level}`, newLevel);
                    });
                },
                getParent() {
                    const {
                        options: { branchSelector },
                    } = treeSortable;
                    const level = $(this).getBranchLevel() || 1;
                    let $prev = $(this).prev(branchSelector);

                    while ($prev.length && $prev.getBranchLevel() >= level) {
                        $prev = $prev.prev(branchSelector);
                    }

                    return $prev;
                },
                getRootChild() {
                    const {
                        options: { branchSelector, treeSelector, levelPrefix },
                    } = treeSortable;

                    return $(treeSelector).children(`${branchSelector}.${levelPrefix}-1`);
                },
                getLastChild() {
                    const {
                        options: { branchSelector, treeSelector, levelPrefix },
                    } = treeSortable;
                    const $children = $(this).getChildren();
                    const $descendants = $(this).getDescendants();
                    const $lastChild = $descendants.length > $children.length ? $descendants.last() : $children.last();

                    return $lastChild.length ? $lastChild : $();
                },
                getChildren() {
                    const {
                        options: { branchSelector },
                    } = treeSortable;
                    let $children = $();

                    this.each(function () {
                        let level = $(this).getBranchLevel() || 1,
                            $next = $(this).next(branchSelector);

                        while ($next.length && $next.getBranchLevel() > level) {
                            if ($next.getBranchLevel() === level + 1) {
                                $children = $children.add($next);
                            }
                            $next = $next.next(branchSelector);
                        }
                    });

                    return $children;
                },
                getDescendants() {
                    const {
                        options: { branchSelector },
                    } = treeSortable;
                    let $descendants = $();

                    this.each(function () {
                        let level = $(this).getBranchLevel() || 1,
                            $next = $(this).next(branchSelector);

                        while ($next.length && $next.getBranchLevel() > level) {
                            $descendants = $descendants.add($next);
                            $next = $next.next(branchSelector);
                        }
                    });

                    return $descendants;
                },
                nextBranch() {
                    return $(this).next();
                },
                prevBranch() {
                    return $(this).prev();
                },
                nextSibling() {
                    const {
                        options: { branchSelector },
                    } = treeSortable;

                    let level = $(this).getBranchLevel() || 1,
                        $next = $(this).next(branchSelector),
                        nextLevel = $next.getBranchLevel();

                    while ($next.length && nextLevel > level) {
                        $next = $next.next(branchSelector);
                        nextLevel = $next.getBranchLevel();
                    }

                    return +nextLevel === +level ? $next : $();
                },
                prevSibling() {
                    const {
                        options: { branchSelector },
                    } = treeSortable;
                    let level = $(this).getBranchLevel() || 1,
                        $prev = $(this).prev(branchSelector),
                        prevLevel = $prev.getBranchLevel();

                    while ($prev.length && prevLevel > level) {
                        $prev = $prev.prev(branchSelector);
                        prevLevel = $prev.getBranchLevel();
                    }

                    return prevLevel === level ? $prev : $();
                },
                getLastSibling() {
                    let $nextSibling = $(this).nextSibling();

                    if (!$nextSibling.length) {
                        return $(this);
                    }

                    while ($nextSibling.length) {
                        $temp = $nextSibling.nextSibling();

                        if ($temp.length) {
                            $nextSibling = $temp;
                        } else {
                            return $nextSibling;
                        }
                    }
                },
                getSiblings(level = null) {
                    const {
                        options: { treeSelector, branchSelector },
                    } = treeSortable;
                    level = level || $(this).getBranchLevel();

                    let $siblings = [],
                        $branches = $(`${treeSelector} > ${branchSelector}`),
                        $self = this;

                    $branches.length &&
                        $branches.each(function () {
                            let branchLevel = $(this).getBranchLevel();

                            if (+branchLevel === +level && $self[0] !== $(this)[0]) {
                                $siblings.push($(this));
                            }
                        });

                    return $siblings;
                },
                calculateSiblingDistances() {
                    const {
                        options: { branchSelector, branchPathSelector },
                    } = treeSortable;
                    $(branchSelector).each(function () {
                        const level = $(this).getBranchLevel() || 1;
                        $(this).find(branchPathSelector).show();

                        if (typeof $(this).nextSibling !== "function") return;

                        if (level > 1) {
                            const $sibling = $(this).nextSibling();

                            /**
                             * If next sibling (siblings with same branch level) exists then
                             * calculate the distance between two siblings and set the path
                             * height according to the distance.
                             */
                            if ($sibling.length) {
                                const distance = getDistance($(this).get(0), $sibling.get(0));
                                $sibling
                                    .find(branchPathSelector)
                                    .css("height", `${Math.max(distance.distanceY + 8, 55)}px`);
                            } else {
                                /**
                                 * If no sibling exists to a branch then find the child.
                                 * If child exists then set the child height as the default 55px.
                                 */
                                const $nextBranch = $(this).next(branchSelector);
                                const nextBranchLevel = $nextBranch.getBranchLevel() || 1;

                                const isChild = $nextBranch.length > 0 && nextBranchLevel > level;

                                if (isChild) {
                                    $nextBranch.find(branchPathSelector).css("height", "55px");
                                }
                            }
                        } else {
                            $(this).find(branchPathSelector).hide();
                        }
                    });
                },
                addChildBranch() {
                    const {
                        options: {
                            treeSelector,
                            branchSelector,
                            dataAttributes: { id },
                            maxLevel,
                        },
                        generateUUID,
                        createBranch,
                        updateBranchZIndex,
                    } = treeSortable;

                    $branch = $(this).closest(`${treeSelector} ${branchSelector}`);

                    if (!$branch.length) {
                        throw Error("Invalid selector! Make sure that your add child button is inside a branch.");
                    }

                    const uid = generateUUID();
                    const parent_id = $branch.data(id);
                    const level = Math.min(maxLevel, parseInt($branch.getBranchLevel()) + 1);
                    const title = "New Branch " + uid;

                    $lastChild = $branch.getLastChild();

                    const $element = createBranch({ id: uid, parent_id, title, level });

                    if ($lastChild.length) {
                        $lastChild.after($element);
                    } else {
                        $branch.after($element);
                    }

                    $(treeSelector).calculateSiblingDistances();
                    updateBranchZIndex();
                },

                addSiblingBranch() {
                    const {
                        options: {
                            treeSelector,
                            branchSelector,
                            dataAttributes: { parent },
                        },
                        generateUUID,
                        createBranch,
                        updateBranchZIndex,
                    } = treeSortable;

                    const $branch = $(this).closest(`${treeSelector} ${branchSelector}`);
                    const uid = generateUUID();
                    const parent_id = $branch.data(parent);
                    const level = $branch.getBranchLevel();
                    const title = "New Branch " + uid;
                    const $lastSibling = $branch.getLastSibling();
                    let $lastChild = $lastSibling.getLastChild();

                    while ($lastChild.length) {
                        $temp = $lastChild.getLastChild();

                        if ($temp.length) {
                            $lastChild = $temp;
                        } else {
                            break;
                        }
                    }

                    const $element = createBranch({ id: uid, parent_id, level, title });

                    if ($lastChild.length) {
                        $lastChild.after($element);
                    } else {
                        $lastSibling.after($element);
                    }

                    $(treeSelector).calculateSiblingDistances();
                    updateBranchZIndex();
                },

                removeBranch() {
                    const {
                        options: { treeSelector, branchSelector },
                        updateBranchZIndex,
                    } = treeSortable;

                    const $branch = $(this).closest(`${treeSelector} ${branchSelector}`);
                    $descendants = $branch.getDescendants();

                    $descendants.each((_, element) => {
                        $(element).remove();
                    });

                    $branch.remove();
                    updateBranchZIndex();
                },
            });
        },

        updateBranchZIndex() {
            const {
                options: { treeSelector, branchSelector },
            } = treeSortable;
            const $branches = $(`${treeSelector} > ${branchSelector}`);
            const length = $branches.length;

            $branches.length &&
                $branches.each(function (index) {
                    $(this).css("z-index", Math.max(1, length - index));
                });
        },
        initSorting() {
            const { options, pxToNumber, numberToPx, updateBranchZIndex } = treeSortable;
            const {
                treeSelector,
                dragHandlerSelector,
                placeholderName,
                childrenBusSelector,
                branchPathSelector,
                branchSelector,
                levelPrefix,
                dataAttributes,
                maxLevel,
            } = options;

            updateBranchZIndex();

            /** Store the current level, for sorting the item after stop dragging. */
            let currentLevel = 1,
                originalLevel = 1,
                childrenBus = null,
                helperHeight = 0,
                originalIndex = 0;

            /** Render the branch paths initially. */
            $(this).calculateSiblingDistances();

            /** Update the placeholder branch level by new level. */
            const updatePlaceholder = (placeholder, level) => {
                placeholder.updateBranchLevel(level);
                currentLevel = level;
            };

            /** Check if we can swap items vertically for branch with children */
            const canSwapItems = ui => {
                let offset = ui.helper.offset(),
                    height = offset.top + helperHeight,
                    nextBranch = ui.placeholder.nextBranch(),
                    nextBranchOffset = nextBranch.offset() || 0,
                    nextBranchHeight = nextBranch.outerHeight();

                return height > nextBranchOffset.top + nextBranchHeight / 3;
            };

            $(treeSelector).sortable({
                handle: dragHandlerSelector,
                placeholder: placeholderName,
                items: "> *",
                start(_, ui) {
                    /** Synchronize the placeholder level with the item's level. */
                    const level = ui.item.getBranchLevel();
                    ui.placeholder.updateBranchLevel(level);
                    originalIndex = ui.item.index();

                    /**  Store the original level. */
                    originalLevel = level;

                    /** Fill the children bus with the children. */
                    childrenBus = ui.item.find(childrenBusSelector);
                    childrenBus.append(ui.item.next().getDescendants());

                    /**
                     * Calculate the placeholder width & height according to the
                     * helper's width & height respectively.
                     */
                    let height = childrenBus.outerHeight();
                    let placeholderMarginTop = ui.placeholder.css("margin-top");

                    height += height > 0 ? pxToNumber(placeholderMarginTop) : 0;
                    height += ui.helper.outerHeight();
                    helperHeight = height;
                    height -= 2;

                    let width = ui.helper.find(branchSelector).outerWidth() - 2;
                    ui.placeholder.css({ height, width });

                    const tmp = ui.placeholder.nextBranch();
                    tmp.css("margin-top", numberToPx(helperHeight));
                    ui.placeholder.detach();
                    $(this).sortable("refresh");
                    ui.item.after(ui.placeholder);
                    tmp.css("margin-top", 0);

                    // Set the current level by the initial item's level.
                    currentLevel = level;
                    $(`${treeSelector} ${branchSelector} ${branchPathSelector}`).hide();
                },
                sort(_, ui) {
                    const { options, getTreeEdge } = treeSortable;
                    const { depth, maxLevel } = options;
                    let treeEdge = getTreeEdge(),
                        offset = ui.helper.offset(),
                        currentBranchEdge = offset.left,
                        lowerBound = 1,
                        upperBound = maxLevel;

                    /**
                     * Calculate the upper bound. The upper bound would be,
                     * the minimum value between the
                     * (previous branch level + 1) and the maxLevel.
                     */
                    let prevBranch = ui.placeholder.prevBranch();
                    prevBranch = prevBranch[0] === ui.item[0] ? prevBranch.prevBranch() : prevBranch;

                    let prevBranchLevel = prevBranch.getBranchLevel();
                    upperBound = Math.min(prevBranchLevel + 1, maxLevel);

                    /**
                     * Calculate the lower bound. The lower bound would be,
                     * the maximum value between the
                     * Next Sibling Level and 1
                     */
                    let nextSibling = ui.placeholder.nextSibling(),
                        placeholderLevel = 1;

                    if (nextSibling.length) {
                        placeholderLevel = ui.placeholder.getBranchLevel() || 1;
                    } else {
                        /**
                         * If no sibling found then
                         * the placeholder level would be the next branch's level.
                         */
                        let nextBranch = ui.placeholder.nextBranch();
                        placeholderLevel = nextBranch.getBranchLevel() || 1;
                    }

                    lowerBound = Math.max(1, placeholderLevel);

                    /**
                     * Calculate the position which is the current helper offset left
                     * minus the tree parent's offset left.
                     * Find the changed level by dividing the position by depth value.
                     *
                     * The final valid changed level would be a value
                     * between upper and lower bound inclusive.
                     */
                    let position = Math.max(0, currentBranchEdge - treeEdge);
                    let newLevel = Math.floor(position / depth) + 1;
                    newLevel = Math.max(lowerBound, Math.min(newLevel, upperBound));

                    if (canSwapItems(ui)) {
                        let nextBranch = ui.placeholder.nextBranch();

                        if (nextBranch.getDescendants().length) {
                            newLevel = nextBranch.getBranchLevel() + 1;
                        }

                        nextBranch.after(ui.placeholder);
                        $(this).sortable("refreshPositions");
                    }

                    /** Update the placeholder position by the changed level. */
                    updatePlaceholder(ui.placeholder, newLevel);
                },
                change(_, ui) {
                    let prevBranch = ui.placeholder.prevBranch();

                    prevBranch = prevBranch[0] === ui.item[0] ? prevBranch.prevBranch() : prevBranch;

                    /**
                     * After changing branches bound the placeholder to the
                     * changed boundary.
                     */
                    let prevBranchLevel = prevBranch.getBranchLevel() || 1;

                    if (prevBranch.length) {
                        ui.placeholder.detach();
                        let children = prevBranch.getDescendants();
                        if (children && children.length) prevBranchLevel += 1;
                        ui.placeholder.updateBranchLevel(prevBranchLevel);
                        prevBranch.after(ui.placeholder);
                    }
                },
                stop(_, ui) {
                    $(`${branchSelector}:not(${levelPrefix}-1) ${branchPathSelector}`).show();

                    /**
                     * Place the children after the sorted item,
                     * and clear the children bus.
                     */
                    const children = childrenBus.children().insertAfter(ui.item);
                    childrenBus.empty();

                    /** Update the item by currently changed level. */
                    ui.item.updateBranchLevel(currentLevel);
                    children.shiftBranchLevel(currentLevel - originalLevel);

                    /**
                     * Trigger `sortCompleted` event if the level changed or index changed.
                     * i.e. if the items sorted then trigger the event.
                     */
                    if (currentLevel !== originalLevel || originalIndex !== ui.item.index()) {
                        $(document).trigger("sortCompleted", [ui]);
                    }

                    // Calculate the sibling distance after sorting
                    $(this).calculateSiblingDistances();

                    updateBranchZIndex();

                    /** Update the parent ID after sorting. */
                    const $parent = ui.item.getParent();
                    ui.item
                        .data(dataAttributes.parent, $parent.data(dataAttributes.id))
                        .attr(`data-${dataAttributes.parent}`, $parent.data(dataAttributes.id));
                },
            });
        },
    };

    return treeSortable;
}
