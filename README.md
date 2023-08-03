# Zoomable view of a phylogenetic tree


`php rows.php` generates the SVG files for each node, one for leaves and two (open and closed) for internals.



## Overview

- Page described a series of different types of visualisations of trees.
- This approach is a collapsible viewer
- Needs to solve problem of efficiently drawing a collapsable tree
- Need to automatically compute layout for different zoom levels
- Need a notion of node importance for that computation
- Need to compute internal node labels if they are missing
- Need to transition labels between zoom levels, e.g. transition between leaf and internal node labels
- Need to explore partitions as way to compute vertical bars for visualisation
- Need to render each zoom level on web, and display transitions between zoom levels in a nice way.



## Notes

### 2023-07-07

OK, not tiles but pages (just like PDFs). Page numbers go up in powers of two. For n leaves we have 2n+1 nodes in total (which is an odd number) so there will be some space at top and bottom. Zoom level doubles the number of pages. 

Can we compute position of current node after zooming in and zooming out? If we know the row numbers going forward and backwards it becomes easy to automatically scroll to equivalent position in tree display.

With pages can also do lazy loading (can we also do lazy unloading?)

### 2023-07-03

- Need to think about tiling to handle lots of rows, would be motivation for zooming in powers of two so we completely cover the tiles.

- [Implicit In-order Forests: Zooming a billion trace events at 60fps](https://thume.ca/2021/03/14/iforests/) - interesting article on zooming in on a large list of objects. Part of the data structure involves adding new items (e.g., list is a stream of data), but still relevant.

### 2022-11-08

- Thinking about partitioning internal labels to display on right of tree. Requires solving [Maximum disjoint set](https://en.wikipedia.org/wiki/Maximum_disjoint_set) problem, which is straightforward in 1-dimensional case (i.e., lines). `intervals.php` has some simple code for this. So we then need to have a set of intervals (e.g., inorder number) for internal node labels and then find maximum disjoint set. Need to figure out a rule for creating a candidate list of labels/partitions.

### 2022-10-11

- Tried some of the large caddisfly trees, the big tree broke web browsers by having too many images to load for larger trees, so we will need tiling/lazy loading ASAP for that to work.
- We need to be able to reroot and ladderise trees for a better appearance
- Need algorithms for internal labels
- Need to have a working version of a zoomable browser so can decide what other information we need. Specifically, how do we zoom up and down and retain placement for the tree?

### 2022-09-30 
- Now outputs subtree as list of nodes ordered by sequence in which they are added to grow the subtree. We should be able to take this list and recreate the subtree.
- If we store the score for each node at the start then we could explore what happens when we favour a node (e.g., the BLAST query) or spurred a node (e.g., preventing a large but uninteresting clade from every being opened automatically.

### Challenges and questions

- How do we handle labelling internal nodes, especially as we make transitions in zoom levels? Need to think about levels of detail.
- When do we get to actually make a working viewer?????

## Basic idea

- Traverse tree INORDER 
- This means we can draw each node in its own row, independent from any other node (so long as we include the “crossings” below that node)
- This in turn means that zooming or collapsing a tree is simply adding or removing rows
- Hence we can pre-generate “glyphs” for each node based on two states: “open” leaves and internals, and “closed” for internals only. Open shows the node, if it is internal it also has connections to its two children. Closed shows a representation of the closed subtree rooted at that internal node.
- For a given zoom level we compute the subtree to display using a scoring function (how “important” is the node) and a priority queue (see below).
- The display is simply the INORDER list of nodes in the subtree, together with their state (“open” or “closed”).


## Draw

View state is ordered list of nodes and open/closed state, draw that 
- to do: think about maintaining relative position in a scrolled view 

## Current view

Current view is current tree, priority queue, and history(?)

## History

List of nodes opened/closed

## Zoom whole tree

1. Compute how many rows to existing view 
2. Visit each unopened leaf node, add children to priority queue, continue to resolve until number of rows reached. Each node popped from priority queue is added to history

## Zoom one node

1. Add children of node to priority queue, continue to resolve by adding descendants to priority queue until zoom size reached.

## User has target taxon

1. Get path from root to target taxon
2. At any level always ensure child on this path is at top of queue (i.e., assign maximum possible weight)

## Minimise tree

Reverse opening of nodes (does this correspond to rewinding priority queue)?

## Close node

## Functions needed

1. Get list of unopened leaf nodes rooted on node (by default root of tree).
2. Get score for node
3. Get path from root to target node

## Notes

### Scoring nodes

Chen et al. 2009 provide a scoring function based on “information content”.

### Taxonomic labels

Could assign taxonomic labels to internal nodes based on classification of each sequence, then mark nodes where taxonomic labels change (e.g., genus name changes to species). This would give internal labels that are meaningful. See similar approach in Kozlov et al. 2016.

### Colouring schemes

See “Tree Colors: Color Schemes for Tree-Structured Data” [10.1109/tvcg.2014.2346277] 

## Background

See for example “PhyloGeoTool: interactively exploring large phylogenies in an epidemiological context” [10.1093/bioinformatics/btx535]

> We here present an algorithm that partitions the binary phylogenetic tree into clusters using a recursive approach. Combining such an approach to identify clusters of sequences with a progressive zooming approach ensures an efficient and interactive visual navigation of the entire phylogenetic tree. To partition a binary tree 𝒯 into k clusters, the following algorithm was devised. Intuitively, the binary tree is partioned recursively using the cluster sizes as clustering criterium. Starting at the root of the tree 𝒯, the first cluster consists of its left child and all its descendants (i.e. the ‘left’ part of 𝒯), while the second cluster consists of its right child and all its descendants (i.e. the ‘right’ part of 𝒯). These clusters are added to a set 𝒞, that is ordered by descending cluster size (i.e. the number of tree leaves that each cluster covers). The largest cluster from 𝒞 is removed and its corresponding tree is split at the root, creating two new clusters corresponding to the resulting subtrees. These two new clusters are subsequently added to 𝒞. This process is repeated until the maximum number of clusters is reached (i.e. |𝒞| = k).

Essentially the same idea is presented in Zaslavsky et al., who also have some nice ideas on representing within-collapsed clade structure.

> Jin Chen, MacEachren, A. M., & Peuquet, D. J. (2009). Constructing Overview + Detail Dendrogram-Matrix Views. IEEE Transactions on Visualization and Computer Graphics, 15(6), 889–896. https://doi.org/10.1109/tvcg.2009.130

> Kozlov, A. M., Zhang, J., Yilmaz, P., Glöckner, F. O., & Stamatakis, A. (2016). Phylogeny-aware identification and correction of taxonomically mislabeled sequences. Nucleic acids research, 44(11), 5022–5033. https://doi.org/10.1093/nar/gkw396

> Libin, P., Vanden Eynden, E., Incardona, F., Nowé, A., Bezenchek, A., … Sönnerborg, A. (2017). PhyloGeoTool: interactively exploring large phylogenies in an epidemiological context. Bioinformatics, 33(24), 3993–3995. doi:10.1093/bioinformatics/btx535

> Tennekes, M., & de Jonge, E. (2014). Tree Colors: Color Schemes for Tree-Structured Data. IEEE Transactions on Visualization and Computer Graphics, 20(12), 2072–2081. doi:10.1109/tvcg.2014.2346277

> Tennekes, M., & de Jonge, E. (2015). Errata to “Tree Colors: Color Schemes for Tree-Structured Data.” IEEE Transactions on Visualization and Computer Graphics, 21(1), 136–136. doi:10.1109/tvcg.2014.2368383

> Zaslavsky, L., Bao, Y. & Tatusova, T.A. Visualization of large influenza virus sequence datasets using adaptively aggregated trees with sampling-based subscale representation. BMC Bioinformatics 9, 237 (2008). https://doi.org/10.1186/1471-2105-9-237

> Zaslavsky L., Bao Y., Tatusova T.A. (2007) An Adaptive Resolution Tree Visualization of Large Influenza Virus Sequence Datasets. In: Măndoiu I., Zelikovsky A. (eds) Bioinformatics Research and Applications. ISBRA 2007. Lecture Notes in Computer Science, vol 4463. Springer, Berlin, Heidelberg. https://doi.org/10.1007/978-3-540-72031-7_18





