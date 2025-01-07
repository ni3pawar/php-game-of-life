<?php declare(strict_types = 1);

namespace Life;

class Game
{
    private int $iterationsCount;

    private int $size;

    private int $species;

    /**
     * @var int[][]|null[][]
     * Array of available cells in the game with size x size dimensions
     * Indexed by y coordinate and than x coordinate
     */
    private array $cells;

    public function run(string $inputFile, string $outputFile): void
    {
        // Load the input file data once
        $input = new XmlFileReader($inputFile);
        $output = new XmlFileWriter($outputFile);
        
        [$size, $species, $cells, $iterationsCount] = $input->loadFile();

        $this->size = $size;
        $this->species = $species;
        $this->cells = $cells;
        $this->iterationsCount = $iterationsCount;

        // Loop for the required iterations
        for ($i = 0; $i < $this->iterationsCount; $i++) {
            // Create the new cells array in advance
            $newCells = array_fill(0, $this->size, array_fill(0, $this->size, null));

            // Iterate through each cell and apply the evolution
            for ($y = 0; $y < $this->size; $y++) {
                for ($x = 0; $x < $this->size; $x++) {
                    $newCells[$y][$x] = $this->evolveCell($x, $y);

                    // Print the cell's species or a dot (.) for an empty cell
                    echo ($newCells[$y][$x] === null) ? ' ' : $newCells[$y][$x];
                    echo ' ';  // Space between cells

                }
                echo "\n";  // Newline after each row
            }
            echo "\n";  // Extra newline for readability

            // Move to (0,0).
            echo "\033[0;0H";

            // Assign the new cells after the iteration
            $this->cells = $newCells;
        }

        echo "Game World (Size: {$this->size} x {$this->size}, Species: {$this->species}):\n";

        // Save the result to the output file
        $output->saveWorld($this->size, $this->species, $this->cells);
    }

    private function evolveCell(int $x, int $y): ?int
    {
        $cell = $this->cells[$y][$x];
        $neighbours = [];
        
        // Loop through the relative directions of the neighboring cells
        for ($dy = -1; $dy <= 1; $dy++) {
            for ($dx = -1; $dx <= 1; $dx++) {
                // Skip the current cell
                if ($dx === 0 && $dy === 0) {
                    continue;
                }

                $newX = $x + $dx;
                $newY = $y + $dy;

                // Ensure valid cell coordinates
                if ($newX >= 0 && $newX < $this->size && $newY >= 0 && $newY < $this->size) {
                    $neighbours[] = $this->cells[$newY][$newX];
                }
            }
        }

        // Count how many neighbors are the same species as the current cell
        $sameSpeciesCount = 0;
        $speciesCount = array_fill(0, $this->species, 0);

        foreach ($neighbours as $neighbour) {
            if ($neighbour === $cell) {
                $sameSpeciesCount++;
            }
            if ($neighbour !== null) {
                $speciesCount[$neighbour]++;
            }
        }

        // Check if the current cell remains unchanged
        if ($cell !== null && $sameSpeciesCount >= 2 && $sameSpeciesCount <= 3) {
            return $cell;
        }

        // Find species for birth (those with exactly 3 neighbors)
        $speciesForBirth = [];
        foreach ($speciesCount as $speciesIndex => $count) {
            if ($count === 3) {
                $speciesForBirth[] = $speciesIndex;
            }
        }

        // If there are species for birth, return a random one
        if (count($speciesForBirth) > 0) {
            return $speciesForBirth[array_rand($speciesForBirth)];
        }

        return null;
    }

}
