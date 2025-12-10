#!/usr/bin/env python3
"""
Solves Integer Linear Programming problem: minimize sum(x) subject to Ax = b, x >= 0.

Reads problem data from stdin as JSON:
{
    "A": [[...], ...],  # Constraint matrix (counters × buttons)
    "b": [...]          # Target vector (joltage requirements)
}

Outputs the minimum sum as an integer.
"""

import sys
import json
from scipy.optimize import linprog
import numpy as np

def main():
    try:
        # Read input from stdin
        data = json.load(sys.stdin)
        A = np.array(data['A'], dtype=int)
        b = np.array(data['b'], dtype=int)
        
        # Setup ILP problem
        # Objective: minimize sum(x) where x is vector of button presses
        num_buttons = len(A[0])
        c = np.ones(num_buttons)  # Minimize sum(x)
        
        # Constraints: Ax = b, x >= 0
        A_eq = A
        b_eq = b
        bounds = [(0, None)] * num_buttons  # x >= 0 (non-negative integers)
        
        # Solve using integer linear programming
        result = linprog(
            c,
            A_eq=A_eq,
            b_eq=b_eq,
            bounds=bounds,
            integrality=True,  # Require integer solutions
            method='highs'     # Use HiGHS solver (default, supports ILP)
        )
        
        if result.success:
            # Return the minimum sum (objective value)
            print(int(result.fun))
        else:
            # No solution found
            print(0)
            sys.exit(1)
            
    except Exception as e:
        # Error handling
        print(0)
        sys.stderr.write(f"Error: {e}\n")
        sys.exit(1)

if __name__ == '__main__':
    main()

