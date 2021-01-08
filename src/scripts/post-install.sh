#!/bin/bash
grep -qx '\-include vendor/turbine/workflow/src/makefiles/Makefile' Makefile || echo '-include vendor/turbine/workflow/src/makefiles/Makefile' >> Makefile
