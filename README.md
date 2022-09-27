# Zoomable view of a phylogenetic tree

Ideas

- Tree in order
- One row per node
- At any given zoom we have a set of rows to draw

- Traverse tree compute all metrics and generate open and closed SVG images


## Draw

View state is ordered list of nodes and pen/closed state, draw that 
- to do: think about maintaining relative position in a scrolled view 

## Current view

Current view is current tree, pq, and history(?)

## History

List of nodes opened/closed

## Zoom whole tree

1. Compute how many rows to existing view 
2. Visit each unopened leaf node, add children to pq, continue to resolve until number of rows reached. Each node popped from pq is added to history

## Zoom one node

1. Add children of node to pq, continue to resolve by adding descendants to pq until zoom size reached.

## User has target taxon

1. Get path from root to target taxon
2. At any level always ensure child on this path is at top of queue (i.e., assign maximum possible weight)

## Minimise tree

Reverse opening of nodes (does this correspond to rewinding pq)?

## Close node

## Functions needed

1. Get list of unopened leaf nodes rooted on node (by default root of tree).
2. Get score for node
3. Get path from root to target node

