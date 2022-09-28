# Zoomable view of a phylogenetic tree

Ideas

- Tree in order
- One row per node
- At any given zoom we have a set of rows to draw

- Traverse tree compute all metrics and generate open and closed SVG images


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





